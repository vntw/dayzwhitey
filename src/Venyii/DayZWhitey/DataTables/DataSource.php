<?php

/*
 * This file is part of DayZWhitey
 *
 * (c) venyii <ven@cersei.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Venyii\DayZWhitey\DataTables;

use Silex\Application;
use Venyii\DayZWhitey\Db;
use Venyii\DayZWhitey\DataTables\Type\TypeInterface;
use Venyii\DayZWhitey\Helper\HtmlUtil;

class DataSource
{
    /**
     * @var \Silex\Application
     */
    private $app;

    /**
     * @var array
     */
    private $types;

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->types = array();
    }

    /**
     * @param TypeInterface $type
     */
    public function registerType(TypeInterface $type)
    {
        $this->types[$type->getId()] = $type;
    }

    /**
     * @param  string $typeId
     * @return bool
     */
    public function hasType($typeId)
    {
        return isset($this->types[$typeId]);
    }

    /**
     * @param  string        $typeId
     * @return TypeInterface
     */
    public function getType($typeId)
    {
        return $this->types[$typeId];
    }

    /**
     * @param  TypeInterface|string $type
     * @return array
     */
    public function collect($type)
    {
        $app = $this->app;

        if (!$type instanceof TypeInterface) {
            $type = $this->getType($type);
        }

        $tableName = $type->getTable();
        $tableColumns = $type->getAllColumns();
        // Indexed column (used for fast and accurate table cardinality)
        $indexColumn = $type->getIndexColumn();

        // Paging
        $queryLimit = '';
        $displayStart = isset($_GET['iDisplayStart']) ? (int) $_GET['iDisplayStart'] : null;
        $displayLength = isset($_GET['iDisplayLength']) ? (int) $_GET['iDisplayLength'] : null;

        if (null !== $displayStart && null !== $displayLength && $displayLength !== -1) {
            $queryLimit = sprintf('LIMIT %d, %d', $displayStart, $displayLength);
        }

        // Ordering
        $sortOrder = '';
        if (isset($_GET['iSortCol_0'])) {
            $sortOrder = 'ORDER BY  ';
            for ($i = 0; $i < (int) $_GET['iSortingCols']; $i++) {
                $sortCol = isset($_GET['iSortCol_' . $i]) ? (int) $_GET['iSortCol_' . $i] : null;

                if (null !== $sortCol && $_GET['bSortable_' . $sortCol] == 'true' && isset($tableColumns[$sortCol]) && null !== $tableColumns[$sortCol]) {
                    $sortOrder .= '`' . $tableColumns[$sortCol] . '` ' . ($_GET['sSortDir_' . $i] === 'asc' ? 'ASC' : 'DESC') . ', ';
                }
            }

            $sortOrder = substr_replace($sortOrder, '', -2);
            if ($sortOrder == 'ORDER BY') {
                $sortOrder = '';
            }
        }

        /*
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */
        $where = '';
        if (isset($_GET['sSearch']) && $_GET['sSearch'] != '') {
            $where = 'WHERE (';
            foreach ($type->getReadableColumns() as $column) {
                $where .= sprintf('%s LIKE \'%%%s%%\' OR ', $column, HtmlUtil::escape($_GET['sSearch']));
            }
            $where = substr_replace($where, '', -3);
            $where .= ')';
        }

        /* Individual column filtering */
        $tableColumnCount = count($tableColumns);

        for ($i = 0; $i < $tableColumnCount; $i++) {
            if (isset($_GET['bSearchable_' . $i]) && $_GET['bSearchable_' . $i] == 'true' && $_GET['sSearch_' . $i] != '') {
                if ($where == '') {
                    $where = 'WHERE ';
                } else {
                    $where .= ' AND ';
                }
                $where .= "`" . $tableColumns[$i] . "` LIKE '%" . HtmlUtil::escape($_GET['sSearch_' . $i]) . "%' ";
            }
        }

        // SQL queries - Get data to display
        $query = sprintf(
            'SELECT SQL_CALC_FOUND_ROWS `%s` FROM %s %s %s %s',
            str_replace(' , ', ' ', implode('`, `', $type->getReadableColumns())),
            $tableName,
            $where,
            $sortOrder,
            $queryLimit
        );

        $rResult = $app['db']->query($query)->fetchAll(Db::FETCH_ASSOC);

        /* Data set length after filtering */
        $foundRowCount = $app['db']->query('SELECT FOUND_ROWS()')->fetchColumn();

        /* Total data set length */
        $countQuery = sprintf('SELECT COUNT(%s) FROM `%s`', $indexColumn, $tableName);
        $totalResultCount = (int) $app['db']->query($countQuery)->fetch(Db::FETCH_COLUMN);

        $output = array(
            'sEcho' => (int) $_GET['sEcho'],
            'iTotalRecords' => $totalResultCount,
            'iTotalDisplayRecords' => $foundRowCount,
            'aaData' => $type->modifyOutputData($app, $rResult)
        );

        return $output;
    }
}
