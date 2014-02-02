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

        $aColumns = $type->getAllColumns();

        /* Indexed column (used for fast and accurate table cardinality) */
        $sIndexColumn = $type->getIndexColumn();

        $sTable = $type->getTable();

        /*
         * Paging
         */
        $sLimit = "";
        if (isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != '-1') {
            $sLimit = "LIMIT " . intval($_GET['iDisplayStart']) . ", " .
                intval($_GET['iDisplayLength']);
        }

        /*
         * Ordering
         */
        $sOrder = "";
        if (isset($_GET['iSortCol_0'])) {
            $sOrder = "ORDER BY  ";
            for ($i = 0; $i < intval($_GET['iSortingCols']); $i++) {
                $sortCol = isset($_GET['iSortCol_' . $i]) ? (int) $_GET['iSortCol_' . $i] : null;

                if (null !== $sortCol && $_GET['bSortable_' . $sortCol] == "true" && isset($aColumns[$sortCol]) && null !== $aColumns[$sortCol]) {
                    $sOrder .= "`" . $aColumns[$sortCol] . "` " . ($_GET['sSortDir_' . $i] === 'asc' ? 'asc' : 'desc') . ", ";
                }
            }

            $sOrder = substr_replace($sOrder, "", -2);
            if ($sOrder == "ORDER BY") {
                $sOrder = "";
            }
        }

        /*
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */
        $sWhere = "";
        if (isset($_GET['sSearch']) && $_GET['sSearch'] != "") {
            $sWhere = "WHERE (";
            foreach ($type->getReadableColumns() as $column) {
                $sWhere .= "`" . $column . "` LIKE '%" . $_GET['sSearch'] . "%' OR ";
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= ')';
        }

        /* Individual column filtering */
        for ($i = 0; $i < count($aColumns); $i++) {
            if (isset($_GET['bSearchable_' . $i]) && $_GET['bSearchable_' . $i] == "true" && $_GET['sSearch_' . $i] != '') {
                if ($sWhere == "") {
                    $sWhere = "WHERE ";
                } else {
                    $sWhere .= " AND ";
                }
                $sWhere .= "`" . $aColumns[$i] . "` LIKE '%" . $_GET['sSearch_' . $i] . "%' ";
            }
        }

        /*
         * SQL queries
         * Get data to display
         */
        $sQuery = "
        SELECT SQL_CALC_FOUND_ROWS `" . str_replace(" , ", " ", implode("`, `", $type->getReadableColumns())) . "`
        FROM   $sTable
        $sWhere
        $sOrder
        $sLimit
        ";

        $rResult = $app['db']->query($sQuery)->fetchAll(Db::FETCH_ASSOC);

        /* Data set length after filtering */
        $sQuery = "SELECT FOUND_ROWS()";
        $iFilteredTotal = $app['db']->query($sQuery)->fetchColumn();

        /* Total data set length */
        $sQuery = "SELECT COUNT(`" . $sIndexColumn . "`) FROM $sTable";
        $iTotal = $app['db']->query($sQuery)->fetchColumn();

        /*
         * Output
         */
        $output = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => $type->modifyOutputData($app, $rResult)
        );

        return $output;
    }
}
