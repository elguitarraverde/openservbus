<?php
/**
 * This file is part of OpenServBus plugin for FacturaScripts
 * Copyright (C) 2022 Carlos Garcia Gomez <carlos@facturascripts.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see http://www.gnu.org/licenses/.
 */

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\EditController;

/**
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class EditReservaTour extends EditController
{
    use OpenServBusControllerTrait;

    public function getModelClassName(): string
    {
        return "ReservaTour";
    }

    public function getPageData(): array
    {
        $data = parent::getPageData();
        $data["title"] = "booking";
        $data["icon"] = "fas fa-calendar-check";
        return $data;
    }

    protected function createViews()
    {
        parent::createViews();
        $this->createViewsSubReservaTour();
        $this->setTabsPosition('bottom');
    }

    protected function createViewsSubReservaTour(string $viewName = "ListSubReservaTour")
    {
        $this->addListView($viewName, "SubReservaTour", "underbooks", "fas fa-calendar-alt");
        $this->views[$viewName]->addOrderBy(["id"], "code", 2);
        $this->views[$viewName]->addSearchFields(["id", "reference"]);

        // ocultar columnas
        $this->views[$viewName]->disableColumn('booking', true);

        // Filtros
        $this->views[$viewName]->addFilterAutocomplete('codcliente', 'agency', 'codcliente', 'clientes', 'codcliente', 'nombre');

        $status = $this->codeModel->all('tour_reservas_estados', 'id', 'name');
        $this->views[$viewName]->addFilterSelect('idestado', 'status', 'idestado', $status);

        // asignamos los colores
        $this->addColorStatusBooking($viewName);
    }

    protected function loadData($viewName, $view)
    {
        $mvn = $this->getMainViewName();
        switch ($viewName) {
            case 'ListSubReservaTour':
                $idreserva = $this->getViewModelValue($mvn, 'id');
                $where = [new DatabaseWhere('idreserva', $idreserva)];
                $view->loadData('', $where);
                break;

            default:
                parent::loadData($viewName, $view);
                break;
        }
    }
}
