#!/usr/local/bin/python3
import sys
import struct
import os

def unescape_str(s):
    first = s.find('"')
    last = s.rfind('"')
    if first != -1 and last >= first:
        content = s[first + 1:last]
    else:
        content = s
    return content.replace('\\\\', '\x00') \
                  .replace('\\n', '\n') \
                  .replace('\\t', '\t') \
                  .replace('\\"', '"') \
                  .replace('\x00', '\\')

def po2mo(po_file, mo_file):
    if not os.path.exists(po_file):
        print(f"Error: {po_file} not found")
        sys.exit(1)

    with open(po_file, 'r', encoding='utf-8') as f:
        lines = f.readlines()

    entries = []
    current_id = None
    current_str = None
    state = None

    for line in lines:
        line = line.strip()
        if not line or line.startswith('#'):
            continue
        if line.startswith('msgid '):
            if current_id is not None and current_str is not None:
                entries.append((current_id, current_str))
            current_id = unescape_str(line[6:])
            current_str = ''
            state = 'id'
        elif line.startswith('msgstr '):
            current_str = unescape_str(line[7:])
            state = 'str'
        elif line.startswith('"') and line.endswith('"'):
            val = unescape_str(line)
            if state == 'id':
                current_id += val
            elif state == 'str':
                current_str += val

    if current_id is not None and current_str is not None:
        entries.append((current_id, current_str))

    # gettext requires entries to be sorted by original string
    entries.sort(key=lambda x: x[0].encode('utf-8'))

    N = len(entries)
    orig_table = []
    trans_table = []
    data = bytearray()
    offset = 28 + N * 16

    for orig, _ in entries:
        b = orig.encode('utf-8') + b'\x00'
        orig_table.append((len(b) - 1, offset))
        data += b
        offset += len(b)

    for _, trans in entries:
        b = trans.encode('utf-8') + b'\x00'
        trans_table.append((len(b) - 1, offset))
        data += b
        offset += len(b)

    header = struct.pack('<Iiiiiii', 0x950412de, 0, N, 28, 28 + N * 8, 0, 0)
    tables = bytearray()
    for l, o in orig_table:
        tables += struct.pack('<II', l, o)
    for l, o in trans_table:
        tables += struct.pack('<II', l, o)

    os.makedirs(os.path.dirname(os.path.abspath(mo_file)), exist_ok=True)
    with open(mo_file, 'wb') as f:
        f.write(header + tables + data)

    print(f"Successfully compiled {N} entries into {mo_file}")

if __name__ == '__main__':
    if len(sys.argv) < 3:
        print("Usage: compile_po.py <input.po> <output.mo>")
        sys.exit(1)
    po2mo(sys.argv[1], sys.argv[2])
