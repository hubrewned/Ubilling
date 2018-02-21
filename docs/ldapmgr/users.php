<?php

$ldapMgrCfg = parse_ini_file(dirname(__FILE__) . '/ldapmgr.ini');
$apiUrl = $ldapMgrCfg['API_URL'];
$apiKey = $ldapMgrCfg['API_KEY'];

class remoteLdapBase {

    protected $apiUrl = '';
    protected $apiKey = '';
    protected $urlInterface = '';

    const USER_CREATE = '/add_member';
    const USER_DEL = '/remove_user';
    const USER_PASSWD = '/change_passwd';

    public function __construct($apiUrl, $apiKey) {
        $this->apiUrl = $apiUrl;
        $this->apiKey = $apiKey;
        if ((!empty($this->apiKey)) AND ( !empty($this->apiUrl))) {
            $this->urlInterface = $this->apiUrl . '?module=remoteapi&key=' . $this->apiKey . '&action=ldapmgr&param=';
            $this->getChangedUsersData();
            $this->getDeletedUsersData();
        } else {
            die('Error reading config file' . "\n");
        }
    }

    protected function getChangedUsersData() {
        $result = array();
        $rawData = file_get_contents($this->urlInterface . 'changedusers');
        if (!empty($rawData)) {
            $tmpArr = json_decode($rawData, true);
            if (!empty($tmpArr)) {
                foreach ($tmpArr as $io => $each) {
                    $userGroups = json_decode($each['groups'], true);
                    shell_exec(dirname(__FILE__) . self::USER_CREATE . ' ' . $each['login']);
                    if (!empty($userGroups)) {
                        foreach ($userGroups as $ia => $eachGroup) {
                            shell_exec(dirname(__FILE__) . self::USER_CREATE . ' ' . $each['login'] . ' ' . $eachGroup);
                        }
                    }
                }
            }
        }
        return ($result);
    }

    protected function getDeletedUsersData() {
        $result = array();
        $rawData = file_get_contents($this->urlInterface . 'deletedusers');
        if (!empty($rawData)) {
            $tmpArr = json_decode($rawData, true);
            if (!empty($tmpArr)) {
                foreach ($tmpArr as $io => $each) {
                    shell_exec(dirname(__FILE__) . self::USER_DEL . ' ' . $each);
                }
            }
        }
        return ($result);
    }

}

$remoteBase = new remoteLdapBase($apiUrl, $apiKey);
?>