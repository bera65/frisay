<?php

class DB
{
	public static function execute($sql, $params = []) 
	{
		global $db;
		try {
			$stmt = $db->prepare($sql); // Sorguyu hazırla
			$stmt->execute($params);    // Parametreleri güvenli bir şekilde gönder
			
			// SELECT sorguları için sonuçları döndür
			if ($stmt->columnCount() > 0) {
				return $stmt->fetchAll(PDO::FETCH_ASSOC);
			}
			// INSERT, UPDATE, DELETE için true/false döndür
			return true; 
			
		} catch (Exception $e) {
			error_log('Query error: ' . $e->getMessage());
			return false;
		}
	}
	public static function getRow($table, $where, $value)
	{
		$table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
		$value = preg_replace('/[^a-zA-Z0-9_]/', '', $value);

		$sql = 'SELECT '.$value.'
				FROM `'.$table.'`
				WHERE '.$where.'
				LIMIT 1';

		$result = self::execute($sql);

		if (!empty($result[0][$value]))
			return $result[0][$value];

		return $result[0][$value] ?? '';
	}
	public static function getAssocRow($table, $where) 
	{
		$value = '*';
		$table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);

		$sql = 'SELECT '.$value.' FROM ' . $table . ' WHERE ' . $where . ' LIMIT 1';

		$result = self::execute($sql);

		if (!empty($result))
			return $result;

		return '';
	}
	
	public static function insert($table, $data)
	{
		global $db;

		$prep = [];
		foreach ($data as $k => $v) {
			$prep[':'.$k] = $v;
		}

		$sql = "INSERT INTO {$table} (".implode(', ', array_keys($data)).") 
				VALUES (".implode(', ', array_keys($prep)).")";

		$sth = $db->prepare($sql);
		$res = $sth->execute($prep);

		if (!$res) {
			error_log('Insert error on ' . $table . ': ' . implode(', ', $sth->errorInfo()));
			return false;
		}

		$lastId = $db->lastInsertId();
		return $lastId ? (int)$lastId : false;
	}

	public static function update($table, $data, $where, $whereParams = []) 
	{
		global $db;
		$prep = [];
		foreach ($data as $k => $v) {
			$prep[] = $k . ' = :' . $k;
		}

		$sql = "UPDATE {$table} SET " . implode(', ', $prep) . " WHERE {$where}";
		$sth = $db->prepare($sql);

		// Parametreleri birleştir (SET + WHERE)
		$params = array_merge($data, $whereParams);

		// SQL log için
		//error_log('Update SQL: ' . $sql . ' | Params: ' . json_encode($params));

		$res = $sth->execute($params);

		if (!$res) {
			error_log('Update error: ' . implode(', ', $sth->errorInfo()));
			return false;
		}
		return $sth->rowCount(); 
	}
	public static function getValue($sql, $params = [])
	{
		$result = self::execute($sql, $params);

		if ($result && isset($result[0])) {
			$row = $result[0];
			return reset($row); // ilk kolonun değerini döndürür
		}
		return false;
	}
	public static function getRowSafe($table, $whereSql, $params = [])
	{
		$sql = "SELECT * FROM {$table} WHERE {$whereSql} LIMIT 1";
		$rows = self::execute($sql, $params);
		return ($rows && isset($rows[0])) ? $rows[0] : false;
	}
}