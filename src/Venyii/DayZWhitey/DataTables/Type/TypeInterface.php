<?php

namespace Venyii\DayZWhitey\DataTables\Type;

use Silex\Application;

interface TypeInterface {

	/**
	 * @return string
	 */
	public function getId();

	/**
	 * @return string
	 */
	public function getTable();

	/**
	 * @return string
	 */
	public function getIndexColumn();

	/**
	 * Array of database columns which should be read and sent back to DataTables.
	 * Use a space where you want to insert a non-database field (for example a counter or static image)
	 *
	 * @return array
	 */
	public function getReadableColumns();

	public function getAllColumns();

	/**
	 * @param Application $app
	 * @param array $data
	 * @return array
	 */
	public function modifyOutputData(Application $app, array $data);

}
