<?php

namespace BizimHesap;

class HttpRequest
{
	/**
	 * @param array{type?: string, data?: mixed} $options
	 * @return array{error: bool, msg?: string, data?: mixed}
	 */
	public function sendRequest(string $url, array $options = []): array
	{
		if (!function_exists('curl_init')) {
			return ['error' => true, 'msg' => 'cURL eklentisi yok'];
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $options['type'] ?? 'POST');
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

		if (isset($options['data'])) {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($options['data'], JSON_UNESCAPED_UNICODE));
		}

		$response = curl_exec($ch);
		$errno = curl_errno($ch);
		$error = curl_error($ch);
		$httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if ($errno || $error !== '') {
			return ['error' => true, 'msg' => 'Bağlantı hatası: ' . $error];
		}

		$response = is_string($response) ? trim($response) : '';

		if ($response === '') {
			return ['error' => true, 'msg' => 'Boş yanıt (HTTP ' . $httpCode . ')'];
		}

		$decoded = json_decode($response, true);

		if (!is_array($decoded)) {
			return ['error' => true, 'msg' => 'HTTP ' . $httpCode . ' — geçersiz JSON yanıt'];
		}

		if (!empty($decoded['Message'])) {
			return ['error' => true, 'msg' => (string) $decoded['Message']];
		}

		if (!empty($decoded['error']) && !is_array($decoded['error'])) {
			return ['error' => true, 'msg' => (string) $decoded['error']];
		}

		if ($httpCode >= 400) {
			$msg = (string) ($decoded['message'] ?? $decoded['msg'] ?? ('HTTP ' . $httpCode));

			return ['error' => true, 'msg' => $msg];
		}

		return ['error' => false, 'data' => $decoded];
	}
}
