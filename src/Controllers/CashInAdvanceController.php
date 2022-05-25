<?php

namespace CashInAdvance\Controllers;

use Plenty\Modules\Webshop\Contracts\ContactRepositoryContract;
use Plenty\Modules\Webshop\Contracts\SessionStorageRepositoryContract;
use Plenty\Plugin\Controller;
use Plenty\Plugin\Templates\Twig;

class CashInAdvanceController extends Controller
{
	public function getBankDetails(Twig $twig, $lang)
	{
		if ($lang === null || !strlen($lang)) {
			$lang = 'de';
		}

		/** @var ContactRepositoryContract $contactRepository */
		$contactRepository = pluginApp(ContactRepositoryContract::class);

		$contactId = $contactRepository->getContactId();

		if ($contactId == 0) {
		    /** @var SessionStorageRepositoryContract $sessionStorage */
		    $sessionStorage = pluginApp(SessionStorageRepositoryContract::class);
		    $lastAccessOrderId = $sessionStorage->getSessionValue(SessionStorageRepositoryContract::LAST_ACCESSED_ORDER);

		    if (is_null($lastAccessOrderId)) {
			return 'Unauthorized';
		    }
		}
		return $twig->render('PrePayment::BankDetailsOverlay', ['lang' => $lang]);
	}
}
