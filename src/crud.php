<?php

namespace Basically;

use DateTime;
use Exception;

class CRUD {
    private static function validateString($var, $against, $errors) {
        if (in_array('string', $against)) {
            if (!is_string($var)) throw new Exception($errors->notString, $errors->notStringCode);
            if (array_key_exists('strlen', $against)) {
                if (isset($against['strlen']['short'])) {
                    if (strlen($var) < $against['strlen']['short']) {
                        throw new Exception($errors->shortString, $errors->shortStringCode);
                    }
                }

                if (isset($against['strlen']['long'])) {
                    if (strlen($var) > $against['strlen']['long']) {
                        throw new Exception($errors->longString, $errors->longStringCode);
                    }
                }
            }

            if (in_array('notags', $against)) {
                $var = strip_tags($var);
            }

            if (array_key_exists('match', $against)) {
                $regex = '/[^'.$against['match'].']/i';
                if (preg_match($regex, $var)) {
                    throw new Exception($errors->stringMatch, $errors->stringMatchCode);
                }
            }

            if (in_array('password', $against)) {
                $var = password_hash($var, PASSWORD_BCRYPT, ['cost' => 11, 'salt' => mcrypt_create_iv(22, MCRYPT_DEV_URANDOM)]);
            }
        }

        return $var;
    }

    private static function validateEmail($var, $against, $errors) {
        if (in_array('email', $against) && !filter_var($var, FILTER_VALIDATE_EMAIL)) {
            throw new Exception($errors->badEmail, $errors->badEmailCode);
        }

        return $var;
    }

    private static function validateNumber($var, $against, $errors) {
        if (in_array('number', $against) && !is_numeric($var)) {
            throw new Exception($errors->notNumber, $errors->notNumberCode);
        }

        return $var;
    }

    private static function validateDate($var, $against, $errors) {
        if (in_array('date', $against)) {
            $format = 'Y-m-d';
            if (array_key_exists('date_format', $against)) {
                $format = $against['date_format'];
            }

            $var = (new DateTime($var))->format($format);
            if (!$var) throw new Exception($errors->badDate, $errors->badDateCode);
        }

        return $var;
    }

    private static function sanitizeXSS($var, $against) {
        if (in_array('xss', $against)) {
            $var = htmlspecialchars($var);
        }

        return $var;
    }

    private static function getName($var, $against, $errors) {
        if (in_array('name', $against)) {
            $var = trim(static::sanitize($var, ['string', 'match' => 'a-z \'\-', 'xss', 'notags']));

            if (strpos($var, ' ') !== false) {
                $parts = explode(' ', $var);
                $last = end($parts);
                array_pop($parts);
                $first = implode(' ', $parts);
                return ['first' => $first, 'last' => $last];
            } elseif (in_array('required-full', $against)) {
                throw new Exception($errors->badName, $errors->badNameCode);
            } else {
                return ['first' => $var, 'last' => ''];
            }
        }

        return $var;
    }

    private static function validateRequired($var, $against, $errors) {
        if (in_array('required', $against)) {
            if (($var == '' || $var == null) && !is_bool($var)) {
                throw new Exception($errors->missingRequired, $errors->missingRequiredCode);
            }
        }

        return $var;
    }

    private static function validateBoolean($var, $against, $errors) {
        if (in_array('boolean', $against) && !is_bool($var)) {
            throw new Exception($errors->notBoolean, $errors->notBooleanCode);
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

    public static function sanitize($var, array $against = [], Errors $errors = null) {
        if (empty($against)) return $var;
        if (!$errors) $errors = new Errors;

        $var = static::validateRequired($var, $against, $errors);
        $var = static::validateEmail($var, $against, $errors);
        $var = static::validateString($var, $against, $errors);
        $var = static::validateNumber($var, $against, $errors);
        $var = static::validateBoolean($var, $against, $errors);
        $var = static::validateDate($var, $against, $errors);
        $var = static::sanitizeXSS($var, $against);
        $var = static::getName($var, $against, $errors);

        return $var;
    }

    public static function insert(\Double\DB $db = null, $table = '', array $data = [], Errors $errors = null) {
        if (!$errors) $errors = new Errors;
        if ($table == '') throw new Exception($errors->noTable, $errors->noTableCode);
        if (empty($data)) throw new Exception($errors->noData, $errors->noDataCode);

        $query = $db->query('insert')
                    ->into($table)
                    ->columns($data['columns'])
                    ->values($data['values'])
                    ->execute();

        if ($query->failed()) {
            throw new Exception($errors->insertFailed, $errors->insertFailedCode);
        }

        return $query->id();
    }

    public static function update(\Double\DB $db = null, $table = '', array $data = [], array $where = [], Errors $errors = null) {
        if (!$errors) $errors = new Errors;
        if ($table == '') throw new Exception($errors->noTable, $errors->noTableCode);
        if (empty($data)) throw new Exception($errors->noData, $errors->noDataCode);
        if (empty($where)) throw new Exception($errors->noClause, $errors->noClauseCode);

        $query = $db->query('update')
            ->table($table);

        foreach ($data['columns'] as $i => $column) {
            $query->set($column, $data['values'][$i]);
        }

        $query->where($where['expression'], $where['data'])
            ->execute();

        if ($query->failed()) {
            throw new Exception($errors->updateFailed, $errors->updateFailedCode);
        }

        return true;
    }

    public static function delete(\Double\DB $db = null, $table = '', array $where = [], Errors $errors = null) {
        if (!$errors) $errors = new Errors;
        if ($table == '') throw new Exception($errors->noTable, $errors->noTableCode);
        if (empty($where)) throw new Exception($errors->noClause, $errors->noClauseCode);

        $query = $db->query('delete')
            ->from($table)
            ->where($where['expression'], $where['data'])
            ->execute();

        if ($query->failed()) {
            throw new Exception($errors->deleteFailed, $errors->deleteFailedCode);
        }

        return true;
    }
}
