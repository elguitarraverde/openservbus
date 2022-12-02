<?php

namespace FacturaScripts\Plugins\OpenServBus\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\EditController;
use FacturaScripts\Plugins\OpenServBus\Model\Collaborator;
use FacturaScripts\Plugins\OpenServBus\Model\Driver;

class EditVehicle extends EditController
{
    public function getModelClassName(): string
    {
        return 'Vehicle';
    }

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['showonmenu'] = false;
        $pageData['menu'] = 'OpenServBus';
        $pageData['title'] = 'vehicle';
        $pageData['icon'] = 'fas fa-bus-alt';
        return $pageData;
    }

    protected function createViews()
    {
        parent::createViews();
        $this->createViewVehicleEquipament();
        $this->createViewVehicleDocumentation();
        $this->setTabsPosition('top');
    }

    protected function createViewVehicleDocumentation($viewName = 'ListVehicleDocumentation')
    {
        $this->addListView($viewName, 'VehicleDocumentation', 'documentation', 'far fa-file-pdf');
        $this->views[$viewName]->addSearchFields(['nombre']);
        $this->views[$viewName]->addOrderBy(['nombre'], 'name', 1);
        $this->views[$viewName]->addOrderBy(['idvehicle', 'nombre'], 'vehicle-type-doc');
        $this->views[$viewName]->addOrderBy(['fecha_caducidad'], 'date-expiration');
        $this->views[$viewName]->addOrderBy(['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->views[$viewName]->addFilterSelect('soloActivos', 'active-all', 'activo', $activo);
        $this->views[$viewName]->addFilterAutocomplete('xIdVehicle', 'vehicle', 'idvehicle', 'vehicles', 'idvehicle', 'nombre');
        $this->views[$viewName]->addFilterAutocomplete('xiddocumentation_type', 'documentation-type', 'iddocumentation_type', 'documentation_types', 'iddocumentation_type', 'nombre');
        $this->views[$viewName]->addFilterPeriod('porFechaCaducidad', 'date-expiration', 'fecha_caducidad');
    }

    protected function createViewVehicleEquipament($viewName = 'ListVehicleEquipament')
    {
        $this->addListView($viewName, 'VehicleEquipament', 'equipment', 'fab fa-accessible-icon');
        $this->views[$viewName]->addOrderBy(['idvehicle', 'idvehicle_equipament_type'], 'vehicle-equipment-plus', 1);
        $this->views[$viewName]->addOrderBy(['fechaalta', 'fechamodificacion'], 'fhigh-fmodiff');

        // Filtros
        $activo = [
            ['code' => '1', 'description' => 'active-yes'],
            ['code' => '0', 'description' => 'active-no'],
        ];
        $this->views[$viewName]->addFilterSelect('soloActivos', 'active-all', 'activo', $activo);

        $this->views[$viewName]->addFilterAutocomplete('xIdVehicle', 'vehicle', 'idvehicle', 'vehicles', 'idvehicle', 'nombre');
        $this->views[$viewName]->addFilterAutocomplete('xIdVehicle_equipament_type', 'equipment-type', 'idvehicle_equipament_type', 'vehicle_equipament_types', 'idvehicle_equipament_type', 'nombre');
    }

    protected function loadData($viewName, $view)
    {
        $mvn = $this->getMainViewName();
        switch ($viewName) {
            case 'ListVehicleDocumentation':
            case 'ListVehicleEquipament':
                $idvehicle = $this->getViewModelValue($mvn, 'idvehicle');
                $where = [new DatabaseWhere('idvehicle', $idvehicle)];
                $view->loadData('', $where);
                break;

            case $mvn:
                parent::loadData($viewName, $view);
                $this->PonerEnVistaLaEdad($viewName);
                $this->loadValuesSelectCollaborators($mvn);
                $this->loadValuesSelectDrivers($mvn);
                break;

            default:
                parent::loadData($viewName, $view);
                break;
        }
    }

    protected function loadValuesSelectCollaborators(string $mvn)
    {
        $column = $this->views[$mvn]->columnForName('collaborator');
        if($column && $column->widget->getType() === 'select') {
            // obtenemos los colaboradores
            $customValues = [];
            $collaboratorsModel = new Collaborator();
            foreach ($collaboratorsModel->all([], [], 0, 0) as $collaborator) {
                $customValues[] = [
                    'value' => $collaborator->idcollaborator,
                    'title' => $collaborator->getProveedor()->nombre,
                ];
            }
            $column->widget->setValuesFromArray($customValues, false, true);
        }
    }

    protected function loadValuesSelectDrivers(string $mvn)
    {
        $column = $this->views[$mvn]->columnForName('usual-driver');
        if($column && $column->widget->getType() === 'select') {
            // obtenemos los conductores
            $customValues = [];
            $driversModel = new Driver();
            foreach ($driversModel->all([], [], 0, 0) as $driver) {
                $customValues[] = [
                    'value' => $driver->iddriver,
                    'title' => $driver->nombre,
                ];
            }
            $column->widget->setValuesFromArray($customValues, false, true);
        }
    }

    protected function PonerEnVistaLaEdad($p_viewName)
    {
        if (!empty($this->views[$p_viewName]->model->fecha_matriculacion_primera)) {
            $intervalo = date_diff(date_create(date("Y-m-d H:i:s"))
                , date_create($this->views[$p_viewName]->model->fecha_matriculacion_primera)
            );
            $this->views[$p_viewName]->model->edad_vehiculo = $intervalo->format('%y a, %m m, %d d');
        }
    }
}