<?php

namespace Venyii\DayZWhitey\Entry;

use Silex\Application;

class Manager {

	/**
	 * @var \Silex\Application
	 */
	private $app;

	/**
	 * @var array
	 */
	private $fields = array('id', 'name', 'email', 'identifier', 'whitelisted');

	/**
	 * @param Application $app
	 */
	public function __construct(Application $app) {
		$this->app = $app;
	}

	/**
	 * @param string $name
	 * @param string $email
	 * @param string $identifier
	 * @return bool
	 */
	public function addEntry($name, $email, $identifier) {
		$stmt = $this->app['db']->prepare("INSERT INTO whitelist (`name`, `email`, `identifier`) VALUES (:name, :email, :identifier)");

		return $stmt->execute(array(
			'name' => $name,
			'email' => $email,
			'identifier' => $identifier
		));
	}

	/**
	 * @param int $id
	 * @param string $name
	 * @param string $email
	 * @param string $identifier
	 * @return bool
	 */
	public function editEntry($id, $name, $email, $identifier) {
		$stmt = $this->app['db']->prepare("UPDATE whitelist SET `name` = :name, `email` = :email, `identifier` = :identifier WHERE `id` = :id");

		return $stmt->execute(array(
			'id' => $id,
			'name' => $name,
			'email' => $email,
			'identifier' => $identifier
		));
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @param string $field
	 * @return bool
	 * @throws \InvalidArgumentException
	 */
	public function editEntryField($id, $value, $field) {
		if (!$this->isValidColumn($field)) {
			throw new \InvalidArgumentException('Invalid column');
		}

		$stmt = $this->app['db']->prepare('UPDATE whitelist SET ' . $field . ' = :value WHERE `id` = :id');

		return $stmt->execute(array(
			'id' => $id,
			'value' => $value
		));
	}

	/**
	 * @param int $id
	 * @return bool
	 */
	public function deleteEntry($id) {
		$id = (int) $id;
		$success = false;

		if ($id > 0) {
			$db = $this->app['db'];
			$stmt = $db->prepare("DELETE FROM whitelist WHERE `id`=:id");
			$stmt->bindParam('id', $id, $db::PARAM_INT);
			$stmt->execute();

			$success = $stmt->rowCount() > 0;
		}

		return $success;
	}

	/**
	 * @param mixed $value
	 * @param string $field
	 * @return array
	 * @throws \InvalidArgumentException
	 */
	public function getEntryBy($value, $field = 'id') {
		if (!$this->isValidColumn($field)) {
			throw new \InvalidArgumentException('Invalid column');
		}

		$db = $this->app['db'];
		$stmt = $db->prepare('SELECT * FROM whitelist WHERE ' . $field . ' = :value');

		$stmt->execute(array(
			'value' => $value
		));

		if ($stmt->rowCount() === 0) {
			return array();
		}

		return $stmt->fetchAll($db::FETCH_ASSOC);
	}

	/**
	 * @param mixed $value
	 * @param string $field
	 * @return bool
	 */
	public function existsEntryBy($value, $field = 'id') {
		$entries = $this->getEntryBy($value, $field);

		return !empty($entries);
	}

	/**
	 * @param string $name
	 * @param string $email
	 * @param string $identifier
	 * @param bool $register
	 * @return array
	 */
	public function validateEntry($name, $email, $identifier, $register = false) {
		$errors = array();

		if (empty($name)) {
			$errors[] = 'Name is empty';
		}
		if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$errors[] = 'Invalid email';
		}
		if (empty($identifier) || strlen($identifier) !== 32) {
			$errors[] = 'GUID is empty or has invalid length';
		}

		if ($register && $this->existsEntryBy($identifier, 'identifier')) {
			$errors[] = 'GUID already exists';
		}

		return $errors;
	}

	/**
	 * @param string $name
	 * @param string $email
	 * @param string $identifier
	 * @param bool $register
	 * @return bool
	 */
	public function isValidEntry($name, $email, $identifier, $register = false) {
		$errors = $this->validateEntry($name, $email, $identifier, $register);

		return empty($errors);
	}

	/**
	 * @param int $id
	 * @param int $status
	 * @return bool
	 */
	public function changeState($id, $status) {
		$stmt = $this->app['db']->prepare("UPDATE whitelist SET whitelisted = :status WHERE id = :id");

		return $stmt->execute(array(
			'id' => $id,
			'status' => (int) $status
		));
	}

	/**
	 * @return PDOStatement
	 */
	public function getWhitelists() {
		$db = $this->app['db'];
		$result = $db->query("SELECT * FROM whitelist");

		return false !== $result ? $result->fetchAll($db::FETCH_ASSOC) : array();
	}

	/**
	 * @return PDOStatement
	 */
	public function getWhitelistsLog() {
		$db = $this->app['db'];
		$result = $db->query("SELECT l.id, l.name, l.GUID, l.`timestamp`, lt.description AS `type` FROM log l INNER JOIN logtypes lt ON l.logtype = lt.id GROUP BY GUID ORDER BY l.id DESC");

		return false !== $result ? $result->fetchAll($db::FETCH_ASSOC) : array();
	}

	/**
	 * @param string $column
	 * @return bool
	 */
	public function isValidColumn($column) {
		return in_array($column, $this->fields);
	}

	/**
	 * @param int $index
	 * @return string|null
	 */
	public function getColumn($index) {
		return isset($this->fields[$index]) ? $this->fields[$index] : null;
	}

}
