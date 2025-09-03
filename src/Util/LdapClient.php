<?php

namespace App\Util;

use Symfony\Component\Ldap\Entry;
use Symfony\Component\Ldap\Ldap;

class LdapClient
{
    private mixed $ldap;

    public function __construct()
    {
        $this->ldap = Ldap::create('ext_ldap', ['connection_string' => 'ldap://localhost:389']);
    }

    public function bind(string $dn, string $password): static
    {
        $this->ldap->bind($dn, $password);
        return $this;
    }

    public function add(string $dn, array $attributes): static
    {
        $entry = new Entry($dn, $attributes);

        $entryManager = $this->ldap->getEntryManager();

        $entryManager->add($entry);
        return $this;
    }
}