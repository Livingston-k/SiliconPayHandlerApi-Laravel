<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DepositeController extends Controller {

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function Process_deposite(Request $request) {
		// return $request;
		$request->validate([
			'currency' => ['required'],
			'phone' => ['required'],
			'amount' => ['required'],
			'txRef' => ['required'],
			'email' => ['required'],
			'call_back' => ['required'],
		]);
		$initamt = str_replace(",", "", $request->amount ?? 0);
		if ($initamt < 500) {
			return response([
				'message' => 'Your deposite amount is very low.',
				'status' => 'failed',
			], 424);
		}
		try {

			$data_req = [
				"req" => "mobile_money",
				"currency" => $request->currency,
				"phone" => $request->phone,
				"encryption_key" => config('services.silicon.encryption'),
				"amount" => $request->amount,
				"emailAddress" => $request->email,
				"decription" => 'Richard',
				"call_back" => $request->call_back,
				"txRef" => $request->txRef,
			];

			$url = "https://silicon-pay.com/process_payments";
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
			curl_setopt($curl, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
			));
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
			curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data_req));
			$resp = curl_exec($curl);
			curl_close($curl);
			$response = json_decode($resp);
			if ($response && $response->status == 'Successful') {
				return response([
					'message' => $response->message,
					'status' => 'success',
				], 200);
			}

			return response([
				'message' => $response->message,
				'status' => $response->status,
			], 424);
		} catch (\Exception $e) {
			return response([
				'message' => 'Error performing action.',
				'status' => 'failed',
			], 424);
		}
	}
}
