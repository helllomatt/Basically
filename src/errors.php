<?php

namespace Basically;

class Errors {
    public $notString = 'not a string';
    public $notStringCode = 0;
    public $shortString = 'string too short';
    public $shortStringCode = 0;
    public $longString = 'string too long';
    public $longStringCode = 0;
    public $stringMatch = 'invalid string match';
    public $stringMatchCode = 0;
    public $badEmail = 'bad email';
    public $badEmailCode = 0;
    public $notNumber = 'not a number';
    public $notNumberCode = 0;
    public $badDate = 'bad date';
    public $badDateCode = 0;
    public $badName = 'first and last name required';
    public $badNameCode = 0;
    public $missingRequired = 'missing required field.';
    public $missingRequiredCode = 0;
    public $notBoolean = 'not a boolean';
    public $notBooleanCode = 0;

    public $noTable = 'no table given';
    public $noTableCode = 0;
    public $noData = 'no data given';
    public $noDataCode = 0;
    public $noClause = 'no clause given';
    public $noClauseCode = 0;

    public $insertFailed = 'insert failed';
    public $insertFailedCode = 0;
    public $updateFailed = 'update failed';
    public $updateFailedCode = 0;
    public $deleteFailed = 'delete failed';
    public $deleteFailedCode = 0;

    public $showQueryError = false;

    public function setWhenNotString($message = '', $code = 0) {
        $this->notString = $message;
        $this->notStringCode = $code;
        return $this;
    }

    public function setWhenShortString($message = '', $code = 0) {
        $this->shortString = $message;
        $this->shortStringCode = $code;
        return $this;
    }

    public function setWhenLongString($message = '', $code = 0) {
        $this->longString = $message;
        $this->longStringCode = $code;
        return $this;
    }

    public function setWhenStringMatch($message = '', $code = 0) {
        $this->stringMatch = $message;
        $this->stringMatchCode = $code;
        return $this;
    }

    public function setWhenBadEmail($message = '', $code = 0) {
        $this->badEmail = $message;
        $this->badEmailCode = $code;
        return $this;
    }

    public function setWhenNotNumber($message = '', $code = 0) {
        $this->notNumber = $message;
        $this->notNumberCode = $code;
        return $this;
    }

    public function setWhenBadDate($message = '', $code = 0) {
        $this->badDate = $message;
        $this->badDateCode = $code;
        return $this;
    }

    public function setWhenBadName($message = '', $code = 0) {
        $this->badName = $message;
        $this->badNameCode = $code;
        return $this;
    }

    public function setWhenMissingRequired($message = '', $code = 0) {
        $this->missingRequired = $message;
        $this->missingRequiredCode = $code;
        return $this;
    }

    public function setWhenNotBoolean($message = '', $code = 0) {
        $this->notBoolean = $message;
        $this->notBooleanCode = $code;
        return $this;
    }

    public function setWhenNoTable($message = '', $code = 0) {
        $this->noTable = $message;
        $this->noTableCode = $code;
        return $this;
    }

    public function setWhenNoData($message = '', $code = 0) {
        $this->noData = $message;
        $this->noDataCode = $code;
        return $this;
    }

    public function setWhenNoClause($message = '', $code = 0) {
        $this->noClause = $message;
        $this->noClauseCode = $code;
        return $this;
    }

    public function setWhenInsertFailed($message = '', $code = 0, $showQueryError = false) {
        $this->insertFailed = $message;
        $this->insertFailedCode = $code;
        $this->showQueryError = $showQueryError;
        return $this;
    }

    public function setWhenUpdateFailed($message = '', $code = 0, $showQueryError = false) {
        $this->updateFailed = $message;
        $this->updateFailedCode = $code;
        $this->showQueryError = $showQueryError;
        return $this;
    }

    public function setWhenDeleteFailed($message = '', $code = 0, $showQueryError = false) {
        $this->deleteFailed = $message;
        $this->deleteFailedCode = $code;
        $this->showQueryError = $showQueryError;
        return $this;
    }
}
