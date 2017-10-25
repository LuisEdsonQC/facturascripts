<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2017  Carlos Garcia Gomez  <carlos@facturascripts.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace FacturaScripts\Core\Lib\RandomDataGenerator;

use FacturaScripts\Core\Model;

/**
 * Description of AccountingGenerator
 *
 * @author Carlos García Gómez
 */
class AccountingGenerator
{

    /**
     * Listado de ejercicios disponibles.
     * @var Model\Ejercicio[] 
     */
    protected $ejercicios;

    /**
     * Empresa por defecto.
     * @var Model\Empresa 
     */
    protected $empresa;

    /**
     * Proporciona acceso al generador de datos.
     * @var DataGeneratorTools
     */
    protected $tools;

    /**
     * 
     * @param Model\Empresa $empresa
     */
    public function __construct($empresa)
    {
        $ejercicioModel = new Model\Ejercicio();
        $this->ejercicios = $ejercicioModel->all();

        $this->empresa = $empresa;
        $this->tools = new DataGeneratorTools();
    }

    public function asientos($max = 50)
    {
        for ($num = 0; $num < $max; ++$num) {
            shuffle($this->ejercicios);

            $asiento = new Model\Asiento();
            $asiento->codejercicio = $this->ejercicios[0]->codejercicio;
            $asiento->concepto = $this->tools->descripcion();
            $asiento->fecha = date('d-m-Y', strtotime($this->ejercicios[0]->fechainicio . ' +' . mt_rand(1, 360) . ' days'));
            $asiento->importe = $this->tools->precio(-999, 150, 99999);
            if (!$asiento->save()) {
                break;
            }
        }

        return $num;
    }

    public function cuentas($max = 50)
    {
        $epigrafes = $this->randomModel("\FacturaScripts\Core\Model\Epigrafe");
        for ($num = 0; $num < $max && count($epigrafes) > 0; ++$num) {
            $cuenta = new Model\Cuenta();
            $cuenta->codcuenta = $epigrafes[0]->codepigrafe . mt_rand(0, 99);
            $cuenta->codejercicio = $epigrafes[0]->codejercicio;
            $cuenta->codepigrafe = $epigrafes[0]->codepigrafe;
            $cuenta->descripcion = $this->tools->descripcion();
            $cuenta->idepigrafe = $epigrafes[0]->idepigrafe;
            if (!$cuenta->save()) {
                break;
            }

            shuffle($epigrafes);
        }

        return $num;
    }

    public function epigrafes($max = 50)
    {
        $grupos = $this->randomModel("\FacturaScripts\Core\Model\GrupoEpigrafes");
        for ($num = 0; $num < $max && count($grupos) > 0; ++$num) {
            $epigrafe = new Model\Epigrafe();
            $epigrafe->codejercicio = $grupos[0]->codejercicio;
            $epigrafe->codepigrafe = $grupos[0]->codgrupo . mt_rand(0, 99);
            $epigrafe->codgrupo = $grupos[0]->codgrupo;
            $epigrafe->descripcion = $this->tools->descripcion();
            $epigrafe->idgrupo = $grupos[0]->idgrupo;
            if (!$epigrafe->save()) {
                break;
            }

            shuffle($grupos);
        }

        return $num;
    }

    public function gruposEpigrafes($max = 50)
    {
        for ($num = 0; $num < $max; ++$num) {
            shuffle($this->ejercicios);

            $grupo = new Model\GrupoEpigrafes();
            $grupo->codejercicio = $this->ejercicios[0]->codejercicio;
            $grupo->codgrupo = mt_rand(1, 99);
            $grupo->descripcion = $this->tools->descripcion();
            if (!$grupo->save()) {
                break;
            }
        }

        return $num;
    }

    protected function randomModel($modelName = "\FacturaScripts\Core\Model\Cuenta")
    {
        $model = new $modelName();
        $data = $model->all();
        if (empty($model)) {
            return [];
        }

        shuffle($data);
        return $data;
    }

    public function subcuentas($max = 50)
    {
        $cuentas = $this->randomModel("\FacturaScripts\Core\Model\Cuenta");
        for ($num = 0; $num < $max; ++$num) {
            $subcuenta = new Model\Subcuenta();
            $subcuenta->codcuenta = $cuentas[0]->codcuenta;
            $subcuenta->coddivisa = $this->empresa->coddivisa;
            $subcuenta->codejercicio = $cuentas[0]->codejercicio;
            $subcuenta->codsubcuenta = $cuentas[0]->codcuenta . mt_rand(0, 9999);
            $subcuenta->descripcion = $this->tools->descripcion();
            $subcuenta->idcuenta = $cuentas[0]->idcuenta;
            if (!$subcuenta->save()) {
                break;
            }

            shuffle($cuentas);
        }

        return $num;
    }
}