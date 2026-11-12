<div class="row pt-2 border-top">
    <!--NUEVO ANTECEDENTE-->
    <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
        <div class="row">
            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" onchange="ag_antecendente();" id="check_antecedentes">
                        <label class="custom-control-label" for="check_antecedentes">Agregar antecedente</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="row" hidden>
            <div class="col-sm-12">
                <div class="alert alert-warning mx-auto" role="alert">
                    <table class="table table-borderless mt-0 mb-0">
                        <tbody>
                            <tr id="tr_obesidad">
                                <td class="align-middle pb-1 pt-1">Obesidad</td>
                                <td class="align-middle pb-1 pt-1">
                                    <button type="button" class="btn  btn-icon btn-danger" data-toggle="tooltip" data-placement="top" title="Quitar">
                                        <i class="feather icon-x"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr id="tr_diabetes">
                                <td class="align-middle pb-1 pt-1">Diabetes</td>
                                <td class="align-middle pb-1 pt-1">
                                    <button type="button" class="btn  btn-icon btn-danger" data-toggle="tooltip" data-placement="top" title="Quitar">
                                        <i class="feather icon-x"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr id="tr_hipertesion">
                                <td class="align-middle pb-1 pt-1">Hipertensión</td>
                                <td class="align-middle pb-1 pt-1">
                                    <button type="button" class="btn  btn-icon btn-danger" data-toggle="tooltip" data-placement="top" title="Quitar">
                                        <i class="feather icon-x"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    {{-- CRONICO --}}
    <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
        <div class="row">
            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" onchange="es_cronico();" id="enf_cronico">
                        <label class="custom-control-label" for="enf_cronico">Control crónico</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="row" hidden>
            <div class="col-sm-12">
                <div class="alert alert-warning mx-auto" role="alert">
                    <table class="table table-borderless mt-0 mb-0">
                        <tbody>
                            <tr id="tr_obesidad">
                                <td class="align-middle pb-1 pt-1">Obesidad</td>
                                <td class="align-middle pb-1 pt-1">
                                    <button type="button" class="btn  btn-icon btn-danger" data-toggle="tooltip" data-placement="top" title="Quitar">
                                        <i class="feather icon-x"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr id="tr_diabetes">
                                <td class="align-middle pb-1 pt-1">Diabetes</td>
                                <td class="align-middle pb-1 pt-1">
                                    <button type="button" class="btn  btn-icon btn-danger" data-toggle="tooltip" data-placement="top" title="Quitar">
                                        <i class="feather icon-x"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr id="tr_hipertesion">
                                <td class="align-middle pb-1 pt-1">Hipertensión</td>
                                <td class="align-middle pb-1 pt-1">
                                    <button type="button" class="btn  btn-icon btn-danger" data-toggle="tooltip" data-placement="top" title="Quitar">
                                        <i class="feather icon-x"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    {{-- Vacunación y desparasitación veterinaria --}}
    <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
        <div class="row">
            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input"
                            id="abrir_vacunacion_desparasitacion"
                            onchange="if (this.checked) { abrirVacunacionVeterinaria(); } else { cerrarVacunacionVeterinaria(); }">
                        <label class="custom-control-label" for="abrir_vacunacion_desparasitacion"
                            onclick="setTimeout(function () { var control = document.getElementById('abrir_vacunacion_desparasitacion'); if (control && control.checked) abrirVacunacionVeterinaria(); }, 0)">
                            Vacunas y desparasitación
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
