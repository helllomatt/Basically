<?php

namespace Basically;

use DateTime;
use Exception;

class CRUD {
    private static function validateString($var, $against) {
        if (in_array('string', $against)) {
            if (!is_string($var)) throw new Exception('not a string');
            if (array_key_exists('strlen', $against)) {
                if (isset($against['strlen']['short'])) {
                    if (strlen($var) < $against['strlen']['short']) {
                        throw new Exception('string too short');
                    }
                }

                if (isset($against['strlen']['long'])) {
                    if (strlen($var) > $against['strlen']['long']) {
                        throw new Exception('string too long');
                    }
                }
            }

            if (in_array('notags', $against)) {
                $var = strip_tags($var);
            }

            if (array_key_exists('match', $against)) {
                $regex = '/[^'.$against['match'].']/i';
                if (preg_match($regex, $var)) {
                    throw new Exception('invalid string match');
                }
            }

            if (in_array('password', $against)) {
                $var = password_hash($var, PASSWORD_BCRYPT, ['cost' => 11, 'salt' => mcrypt_create_iv(22, MCRYPT_DEV_URANDOM)]);
            }
        }

        return $var;
    }

    private static function validateEmail($var, $against) {
        if (in_array('email', $against) && !filter_var($var, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('bad email');
        }

        return $var;
    }

    private static function validateNumber($var, $against) {
        if (in_array('number', $against) && !is_numeric($var)) {
            throw new Exception('not a number');
        }

        return $var;
    }

    private static function validateDate($var, $against) {
        if (in_array('date', $against)) {
            $format = 'Y-m-d';
            if (array_key_exists('date_format', $against)) {
                $format = $against['date_format'];
            }

            $var = (new DateTime($var))->format($format);
            if (!$var) throw new Exception('bad date');
        }

        return $var;
    }

    private static function sanitizeXSS($var, $against) {
        if (in_array('xss', $against)) {
            $var = htmlspecialchars($var);
        }

        return $var;
    }

    private static function getName($var, $against) {
        if (in_array('name', $against)) {
            $var = trim(static::sanitize($var, ['string', 'match' => 'a-z \'\-', 'xss', 'notags']));

            if (strpos($var, ' ') !== false) {
                $parts = explode(' ', $var);
                $last = end($parts);
                array_pop($parts);
                $first = implode(' ', $parts);
                return ['first' => $first, 'last' => $last];
            } elseif (in_array('required-full', $against)) {
                throw new Exception('first and last name required.');
            } else {
                return ['first' => $var, 'last' => ''];
            }
        }

        return $var;
    }

    private static function validateRequired($var, $against) {
        if (in_array('required', $against)) {
            if (($var == '' || $var == null) && !is_bool($var)) {
                throw new Exception('missing required field.');
            }
        }

        return $var;
    }

    private static function validateBoolean($var, $against) {
        if (in_array('boolean', $against) && !is_bool($var)) {
            throw new Exception('not a boolean');
        }

        return $var;
    }

    public static function compile(array $data = []) {
        $cols = [];
        $values = [];

        foreach ($data as $key => $val) {
            $cols[] = $key;
            $values[] = $val;
        }

        return ['columns' => $cols, 'values' => $values];
    }

    public static function sanitize($var, array $against = []) {
        if (empty($against)) return $var;

        $var = static::validateRequired($var, $against);
        $var = static::validateEmail($var, $against);
        $var = static::validateString($var, $against);
        $var = static::validateNumber($var, $against);
        $var = static::validateBoolean($var, $against);
        $var = static::validateDate($var, $against);
        $var = static::sanitizeXSS($var, $against);
        $var = static::getName($var, $against);

        return $var;
    }

    public static function insert(\Double\DB $db = null, $table = '', array $data = []) {
        if ($table == '') throw new Exception('no insert table given');
        if (empty($data)) throw new Exception('no insert data given');

        $query = $db->query('insert')
                    ->into($table)
                    ->columns($data['columns'])
                    ->values($data['values'])
                    ->execute();

        if ($query->failed()) {
            throw new Exception('insert failed');
        }

        return $query->id();
    }

    public static function update(\Double\DB $db = null, $table = '', array $data = [], array $where = []) {
        if ($table == '') throw new Exception('no update table given');
        if (empty($data)) throw new Exception('no update data given');
        if (empty($where)) throw new Exception('no update where clause given');

        $query = $db->query('update')
            ->table($table);

        foreach ($data['columns'] as $i => $column) {
            $query->set($column, $data['values'][$i]);
        }

        $query->where($where['expression'], $where['data'])
            ->execute();

        if ($query->failed()) {
            throw new Exception('update failed');
        }

        return true;
    }

    public static function delete(\Double\DB $db = null, $table = '', array $where = []) {
        if ($table == '') throw new Exception('no delete table given');
        if (empty($where)) throw new Exception('no delete where clause given');

        $query = $db->query('delete')
            ->from($table)
            ->where($where['expression'], $where['data'])
            ->execute();

        if ($query->failed()) {
            throw new Exception('delete failed');
        }

        return true;
    }
}
