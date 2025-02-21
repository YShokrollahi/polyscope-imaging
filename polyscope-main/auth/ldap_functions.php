<?php
require 'config.php';

function find_user_dn($username) {
    $ldapconn = ldap_connect(LDAP_SERVER) or die("Could not connect to LDAP server.");

    ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);

    $bind = ldap_bind($ldapconn, LDAP_ADMIN_USER, LDAP_ADMIN_PASS);

    if ($bind) {
        $search_filter = "(cn={$username})";
        $result = ldap_search($ldapconn, LDAP_SEARCH_BASE, $search_filter, ['cn', 'distinguishedName']);
        $entries = ldap_get_entries($ldapconn, $result);

        if ($entries['count'] > 0) {
            $user_dn = $entries[0]['dn'];
            ldap_unbind($ldapconn);
            return $user_dn;
        } else {
            ldap_unbind($ldapconn);
            return null;
        }
    } else {
        ldap_unbind($ldapconn);
        return null;
    }
}

function verify_password($user_dn, $password) {
    $ldapconn = ldap_connect(LDAP_SERVER) or die("Could not connect to LDAP server.");

    ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);

    $bind = @ldap_bind($ldapconn, $user_dn, $password);

    ldap_unbind($ldapconn);

    return $bind;
}
?>
