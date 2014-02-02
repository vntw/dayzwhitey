<?php

/*
 * This file is part of DayZWhitey
 *
 * (c) venyii <ven@cersei.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Venyii\DayZWhitey\DataTables\Type;

use Silex\Application;

class TypeWhitelistLog implements TypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'wll';
    }

    /**
     * {@inheritdoc}
     */
    public function getTable()
    {
        return 'log';
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexColumn()
    {
        return 'id';
    }

    /**
     * {@inheritdoc}
     */
    public function getReadableColumns()
    {
        return array('id', 'name', 'GUID', 'timestamp', 'logtype');
    }

    /**
     * {@inheritdoc}
     */
    public function getAllColumns()
    {
        return $this->getReadableColumns();
    }

    /**
     * {@inheritdoc}
     */
    public function modifyOutputData(Application $app, array $data)
    {
        $output = array();
        $aColumns = $this->getReadableColumns();
        $logTypes = $app['db']->query('SELECT id, description FROM logtypes ORDER BY id ASC')->fetchAll($app['db']::FETCH_KEY_PAIR);

        foreach ($data as $aRow) {
            $row = array();
            for ($i = 0; $i < count($aColumns); $i++) {
                if ($i === 1) {
                }
                if ($aColumns[$i] == "version") {
                    /* Special output formatting for 'version' column */
                    $row[] = ($aRow[$aColumns[$i]] == "0") ? '-' : $aRow[$aColumns[$i]];
                } elseif ($aColumns[$i] === 'logtype') {
                    $row[] = isset($logTypes[$aRow[$aColumns[$i]]]) ? $logTypes[$aRow[$aColumns[$i]]] : 'n/a';
                } elseif ($aColumns[$i] === 'timestamp') {
                    $row[] = date('d.m.Y H:i:s', strtotime($aRow[$aColumns[$i]]));
                } elseif ($aColumns[$i] != ' ') {
                    /* General output */
                    $row[] = $aRow[$aColumns[$i]];
                }
            }

            $output[] = $row;
        }

        return $output;
    }

}
