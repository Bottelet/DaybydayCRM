<?php

namespace App;

interface Economics
{
	public static function initialize();
	public function createInvoice($params);
	public function bookInvoice($invoiceGuid, $timestamp);
	public function sendInvoice($invoiceGuid, $timestamp);
	public function getContacts();
}
