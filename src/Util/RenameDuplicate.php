<?php

namespace App\Util;

/**
 * Renames the file and adds a sequence to it.
 */
class RenameDuplicate
{
    /**
     * Renames the file and adds a sequence to it.
     *
     * @param string $fileName The file name to be renamed.
     *
     * @return string The renamed filename string.
     */
    public function renameFile(string $fileName) :string
    {
        $result = preg_match('/^.*(\(.*\))\.?.*$/', $fileName);

        if (1 === $result) {
            $fileName = preg_replace_callback(
                '/^(.*)\((.*)\)(\.?.*)$/',
                array($this, 'addSequence'),
                $fileName
            );
        } else {
            $fileName = preg_replace_callback(
                '/^(.*)()(\..*)$/',
                array($this, 'addSequence'),
                $fileName
            );
        }

        return $fileName;
    }

    /**
     * Renames the file and adds a sequence to it.
     *
     * @param array $matches The match components of the regex.
     *
     * @return string The renamed filename string.
     *
     */
    private function addSequence(array $matches) : string
    {
        if ((int)$matches[2] >= 999) {
            throw new \Exception('Sequence is too high!');
        }
        return $matches[1].'('.((int)$matches[2]+1).')'.$matches[3];
    }
}
