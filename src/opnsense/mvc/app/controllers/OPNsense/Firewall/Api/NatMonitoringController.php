<?php

namespace OPNsense\Firewall\Api;

use OPNsense\Base\ApiControllerBase;
use OPNsense\Core\Backend;

class NatMonitoringController extends ApiControllerBase
{
    /**
     * Query Active NAT Sessions từ pfctl state
     */
    public function querySessionsAction()
    {
        $backend = new Backend();
        $response = $backend->configdpRun('filter list states', ['', 5000, 0, '', '']);
        $data = json_decode($response, true);
        $nat_sessions = [];

        if (!empty($data['details'])) {
            foreach ($data['details'] as $row) {
                // Chỉ lấy các session có biến đổi NAT (nat_addr không rỗng)
                if (!empty($row['nat_addr'])) {
                    $nat_sessions[] = [
                        'proto'      => strtoupper($row['proto'] ?? 'TCP'),
                        'src'        => ($row['src_addr'] ?? '') . ':' . ($row['src_port'] ?? ''),
                        'nat'        => ($row['nat_addr'] ?? '') . ':' . ($row['nat_port'] ?? ''),
                        'dst'        => ($row['dst_addr'] ?? '') . ':' . ($row['dst_port'] ?? ''),
                        'state'      => $row['state'] ?? 'ESTABLISHED',
                        'age'        => $row['age'] ?? '',
                        'expires'    => $row['expires'] ?? ''
                    ];
                }
            }
        }

        return [
            'rows'       => $nat_sessions,
            'rowCount'   => count($nat_sessions),
            'total'      => count($nat_sessions),
            'current'    => 1
        ];
    }

    /**
     * Query NAT Traffic Log từ filterlog
     */
    public function queryTrafficAction()
    {
        $backend = new Backend();
        $limit = empty($this->request->get('limit')) ? 1000 : (int)$this->request->get('limit');
        $response = $backend->configdpRun('filter read log', [$limit]);
        $records = json_decode($response, true) ?? [];
        $traffic = [];

        // === THAY THẾ TỪ ĐÂY ===
        foreach ($records as $record) {
            $action  = strtolower($record['action'] ?? '');
            $label   = strtolower($record['label'] ?? '');
            $dstport = $record['dstport'] ?? '';
            $srcport = $record['srcport'] ?? '';
            // Lọc log liên quan tới NAT:
            // 1. Nhãn/hành động chứa từ khoá NAT
            // 2. Gói tin đi qua cổng NAT 8080
            // 3. Gói tin LAN vào cổng 443 do Port Forward 8080 chuyển hướng (anti-lockout)
            $is_nat = ($action === 'nat' || strpos($label, 'nat') !== false || !empty($record['nat_addr'])
                       || $dstport == '8080' || $srcport == '8080'
                       || (strpos($label, 'anti-lockout') !== false && ($record['interface'] ?? '') == 'vtnet1'));
            if ($is_nat) {
                $traffic[] = [
                    'time'      => $record['__timestamp__'] ?? ($record['time'] ?? ''),
                    'interface' => $record['interface'] ?? ($record['if'] ?? ''),
                    'proto'     => strtoupper($record['protoname'] ?? ($record['proto'] ?? 'TCP')),
                    'src'       => ($record['src'] ?? '') . (!empty($record['srcport']) ? ':' . $record['srcport'] : ''),
                    'dst'       => ($record['dst'] ?? '') . (!empty($record['dstport']) ? ':' . $record['dstport'] : ''),
                    'action'    => $record['action'] ?? 'pass',
                    'label'     => !empty($record['label']) ? $record['label'] : 'NAT Port Forward'
                ];
            }
        }
        // === ĐẾN ĐÂY ===
        return [
            'rows'       => $traffic,
            'rowCount'   => count($traffic),
            'total'      => count($traffic),
            'current'    => 1
        ];
    }
}