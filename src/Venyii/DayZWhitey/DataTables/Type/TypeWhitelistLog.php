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
        $columns = $this->getReadableColumns();
        $columnCount = count($columns);
        $logTypes = $app['db']->query('SELECT id, description FROM logtypes ORDER BY id ASC')->fetchAll($app['db']::FETCH_KEY_PAIR);

        foreach ($data as $dataEntry) {
            $row = array();
            for ($i = 0; $i < $columnCount; $i++) {
                if ($columns[$i] === 'logtype') {
                    $row[] = isset($logTypes[$dataEntry[$columns[$i]]]) ? $logTypes[$dataEntry[$columns[$i]]] : 'n/a';
                } elseif ($columns[$i] === 'timestamp') {
                    $row[] = date('d.m.Y H:i:s', strtotime($dataEntry[$columns[$i]]));
                } elseif ($columns[$i] != ' ') {
                    $row[] = $dataEntry[$columns[$i]];
                }
            }

            $output[] = $row;
        }

        return $output;
    }

}
