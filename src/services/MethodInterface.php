<?php
namespace barrelstrength\sproutinvisiblecaptcha\services;

interface MethodInterface
{
    public function verifySubmission();

    public function getProtection();

    public function getField();
}