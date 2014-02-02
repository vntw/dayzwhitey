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
        $aColumns = $this->getReadableColumns();

        foreach ($data as $aRow) {
            $row = array();
            for ($i = 0; $i < count($aColumns); $i++) {
                if ($i === 0) {
                    $active = (int) $aRow['whitelisted'] === 1 ? ' on' : null;
                    $row[] = '<button class="btn btn-standard btn-xs delete-entry"><span class="glyphicon glyphicon-trash"></span></button>';
                    $row[] = '<button class="btn btn-state-toggle toggle-state' . $active . '"></button>';
                }
                if ($aColumns[$i] == "version") {
                    /* Special output formatting for 'version' column */
                    $row[] = ($aRow[$aColumns[$i]] == "0") ? '-' : $aRow[$aColumns[$i]];
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
