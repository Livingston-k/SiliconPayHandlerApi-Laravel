<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WithdrawController extends Controller {
	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function Process_withdraw(Request $request) {
		$request->validate([
			'currency' => ['required'],
			'phone' => ['required'],
			'amount' => ['required'],
			'txRef' => ['required'],
			'email' => ['required'],
			'call_back' => ['required'],
			'reason' => ['required'],
		]);
		$initamt = str_replace(",", "", $request->amount ?? 0);
		$chargeamt = 800;

		if ($initamt <= $chargeamt) {
			return response([
				'message' => 'Your withdraw amount is very less.',
				'status' => 'failed',
			], 424);
		}

		// Sample PHP pay Load
		$data_req = [
			"req" => "mm",
			"currency" => $request->currency,
			"txRef" => $request->txRef,
			"encryption_key" => config('services.silicon.encryption'),
			"amount" => $initamt,
			"emailAddress" => $request->email,
			"call_back" => $request->call_back,
			"phone" => $request->phone,
			"reason" => $request->reason,
			"debit_wallet" => "UGX",
		];
		// Now Create a Signature that you shall pass in the header of your request.
		$secrete_key = config('services.silicon.secret');
		$encryption_key = config('services.silicon.encryption');
		$phone_number = $request->phone;

		$msg = hash('sha256', $encryption_key) . $phone_number;

		$signature = hash_hmac('sha256', $msg, $secrete_key);

		$headers = [
			"signature:" . $signature,
			'Content-Type: application/json',
		];

		$curl = curl_init();
		$url = "https://silicon-pay.com/api_withdraw";
		$curl = curl_init(rawurlencode($url));
		curl_setopt(
			$curl,
			CURLOPT_URL,
			$url
		);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
		curl_setopt($curl, CURLOPT_TIMEOUT, 100);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data_req));
		$resp = curl_exec($curl);
		curl_close($curl);
		$response = json_decode($resp);
		if ($response && $response->status == 'successful') {
			return response([
				'message' => $response->message,
				'status' => $response->status,
			], 200);
		}

		return response([
			'message' => $response->message,
			'status' => $response->status,
		], 424);
	}
}
