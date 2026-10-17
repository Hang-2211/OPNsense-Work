<?php

/*
 * Copyright (C) 2024 Deciso B.V.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 * OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace OPNsense\Firewall\Api;

class OneToOneController extends FilterBaseController
{
    protected static $categorysource = "onetoone.rule";

    public function searchRuleAction()
    {
        $category = (array)$this->request->get('category');

        $filter_funct = function ($record) use ($category) {
            /* categories are indexed by name in the record, but offered as uuid in the selector */
            $catids = !$record->categories->isEmpty() ? $record->categories->getValues() : [];
            return empty($category) || array_intersect($catids, $category);
        };

        $results = $this->searchBase("onetoone.rule", null, "sequence", $filter_funct);

        /* carry results */
        foreach ($results['rows'] as &$record) {
            /* offer list of colors to be used by the frontend */
            $record['category_colors'] = $this->getCategoryColors(
                !empty($record['categories']) ? explode(',', $record['categories']) : []
            );
            /* format "networks" and target */
            foreach (['source_net', 'destination_net'] as $field) {
                if (!empty($record[$field])) {
                    $record["alias_meta_{$field}"] = $this->getNetworks($record[$field]);
                }
            }
        }

        return $results;
    }

    public function setRuleAction($uuid)
    {
        $result = $this->setBase("rule", "onetoone.rule", $uuid);
        if ($result['result'] == 'saved') {
            $this->logAudit('UPDATE', 'One-to-One NAT', $uuid);
        }
        return $result;
    }

    public function addRuleAction()
    {
        $result = $this->addBase("rule", "onetoone.rule");
        if ($result['result'] == 'saved') {
            $this->logAudit('ADD', 'One-to-One NAT', $result['uuid'] ?? '');
        }
        return $result;
    }

    public function getRuleAction($uuid = null)
    {
        return $this->setCopySequence(
            $this->getBase('rule', 'onetoone.rule', $uuid),
            $this->getModel()->onetoone->rule
        );
    }

    public function delRuleAction($uuid)
    {
        $result = $this->delBase("onetoone.rule", $uuid);
        if ($result['result'] == 'deleted') {
            $this->logAudit('DELETE', 'One-to-One NAT', $uuid);
        }
        return $result;
    }

    public function toggleRuleAction($uuid, $enabled = null)
    {
        $result = $this->toggleBase("onetoone.rule", $uuid, $enabled);
        $this->logAudit('TOGGLE', 'One-to-One NAT', $uuid, "state='" . ($result['result'] ?? '') . "'");
        return $result;
    }

    public function moveRuleBeforeAction($selected_uuid, $target_uuid)
    {
        return $this->moveRuleBeforeBase($selected_uuid, $target_uuid, 'onetoone.rule', 'sequence');
    }

    public function toggleRuleLogAction($uuid, $log)
    {
        return $this->toggleRuleLogBase($uuid, $log, 'onetoone.rule');
    }

    public function downloadRulesAction()
    {
        return $this->downloadRulesBase('onetoone.rule');
    }

    public function uploadRulesAction()
    {
        return $this->uploadRulesBase('onetoone.rule');
    }
    
    private function logAudit(string $action, string $type, string $uuid, string $extra = ''): void
    {
        $user = $this->getUserName() ?: 'admin';
        $ip = $this->request->getClientAddress();
        $msg = sprintf(
            "[AUDIT][NAT] user='%s' client_ip='%s' action='%s' type='%s' uuid='%s'%s",
            $user, $ip, $action, $type, $uuid, $extra ? " $extra" : ""
        );
        // 1. Ghi vào General System Log
        syslog(LOG_NOTICE, $msg);
        // 2. Ghi vào Nhật ký quản trị (System > Log Files > Audit)
        openlog("audit", LOG_ODELAY, LOG_AUTH);
        syslog(LOG_NOTICE, $msg);
        closelog();
    }
}
