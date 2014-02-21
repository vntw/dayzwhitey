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

class TypeWhitelist implements TypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'wl';
    }

    /**
     * {@inheritdoc}
     */
    public function getTable()
    {
        return 'whitelist';
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
        return array('id', 'name', 'email', 'identifier', 'whitelisted');
    }

    /**
     * {@inheritdoc}
     */
    public function getAllColumns()
    {
        return array(null, 'whitelisted', 'id', 'name', 'email', 'identifier');
    }

    /**
     * {@inheritdoc}
     */
    public function modifyOutputData(Application $app, array $data)
    {
        $output = array();
        $columns = $this->getReadableColumns();
        $columnCount = count($columns);

        foreach ($data as $dataEntry) {
            $row = array();
            for ($i = 0; $i < $columnCount; $i++) {
                if ($i === 0) {
                    $active = (int) $dataEntry['whitelisted'] === 1 ? ' on' : null;
                    $row[] = '<button class="btn btn-standard btn-xs delete-entry"><span class="glyphicon glyphicon-trash"></span></button>';
                    $row[] = '<button class="btn btn-state-toggle toggle-state' . $active . '"></button>';
                }

                if ($columns[$i] != ' ') {
                    $row[] = $dataEntry[$columns[$i]];
                }
            }

            $output[] = $row;
        }

        return $output;
    }

}
