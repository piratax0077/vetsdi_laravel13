{{-- Formulario único para agregar o editar un familiar del árbol --}}
<div class="modal fade modal-familiar" id="modalFamiliar" tabindex="-1" role="dialog" aria-labelledby="modalFamiliarTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data" action="{{ route('mascotas.genealogia.familiar.guardar', $mascota) }}" id="formFamiliar" class="modal-familiar-form">
                @csrf
                <input type="hidden" name="formulario" value="familiar">
                <input type="hidden" name="modo" id="familiarModo" value="agregar">
                <input type="hidden" name="parentesco" id="familiarParentesco">
                <input type="hidden" name="registro_hermano_id" id="familiarRegistroHermano">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalFamiliarTitulo">Agregar familiar de {{ $mascota->nombre }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                </div>

                <div class="modal-body">
                    <div class="familiar-paso" id="pasoParentesco">
                        <label class="familiar-pregunta" for="selectorParentesco">¿Quién es?</label>
                        <select id="selectorParentesco" class="form-control"></select>
                    </div>

                    <div id="familiarDetalle" hidden>
                        <span class="familiar-pregunta">¿Está registrado en el sistema?</span>
                        <div class="opcion-origen" role="radiogroup" aria-label="Origen del familiar">
                            <label class="opcion-origen-item">
                                <input type="radio" name="origen" value="registrado">
                                <span><i class="fas fa-paw"></i> Está en mis mascotas</span>
                            </label>
                            <label class="opcion-origen-item">
                                <input type="radio" name="origen" value="externo">
                                <span><i class="fas fa-pen"></i> No está registrado</span>
                            </label>
                        </div>

                        <div class="familiar-bloque" id="bloqueRegistrado" hidden>
                            <label for="familiarMascota">Elige la mascota</label>
                            <select name="mascota_id" id="familiarMascota" class="form-control"></select>
                            <small class="familiar-aviso" id="familiarSinOpciones" hidden>
                                No tienes mascotas que coincidan. Puedes ingresarlo como "No está registrado".
                            </small>
                        </div>

                        <div class="familiar-bloque" id="bloqueExterno" hidden>
                            <div class="form-group">
                                <label for="familiarNombre">Nombre</label>
                                <input type="text" name="nombre" id="familiarNombre" class="form-control" maxlength="255">
                            </div>
                            <div class="form-group">
                                <label for="familiarEspecie">Especie</label>
                                <select name="especie" id="familiarEspecie" class="form-control">
                                    <option value="">Seleccione</option>
                                    @foreach(['Canino', 'Felino', 'Otro'] as $opcionEspecie)
                                        <option value="{{ $opcionEspecie }}">{{ $opcionEspecie }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group" id="grupoSexo" hidden>
                                <label for="familiarSexo">Sexo</label>
                                <select name="sexo" id="familiarSexo" class="form-control">
                                    <option value="">Sin indicar</option>
                                    <option value="M">Macho</option>
                                    <option value="F">Hembra</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group familiar-bloque" id="bloqueTipoHermano" hidden>
                            <label for="familiarTipo">Tipo de hermano</label>
                            <select name="tipo" id="familiarTipo" class="form-control">
                                <option value="completo">Hermano completo (mismos padres)</option>
                                <option value="medio_padre">Medio hermano por parte del padre</option>
                                <option value="medio_madre">Medio hermano por parte de la madre</option>
                            </select>
                        </div>

                        <div class="familiar-foto" id="bloqueFoto" hidden>
                            <img class="photo-preview" id="familiarFotoImagen" src="" alt="Foto del familiar" hidden>
                            <span class="photo-preview empty" id="familiarFotoVacia"><i class="fas fa-camera"></i></span>
                            <div class="familiar-foto-texto">
                                <span class="familiar-foto-mensaje" id="familiarFotoMensaje">Foto (opcional)</span>
                                <div id="familiarFotoCampo">
                                    <input type="file" name="foto" id="familiarFoto" class="form-control-file" accept="image/jpeg,image/png,image/webp">
                                    <small class="text-muted">JPG, PNG o WEBP. Máximo 5 MB.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger mr-auto" id="botonQuitarFamiliar" hidden>
                        <i class="fas fa-trash-alt mr-1"></i> Quitar del árbol
                    </button>
                    <button type="button" class="btn btn-outline-dark" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info" id="botonGuardarFamiliar" disabled>
                        <i class="fas fa-save mr-1"></i> Guardar
                    </button>
                </div>
            </form>

            <form method="POST" action="{{ route('mascotas.genealogia.familiar.quitar', $mascota) }}" id="formQuitarFamiliar" hidden>
                @csrf
                @method('DELETE')
                <input type="hidden" name="parentesco">
                <input type="hidden" name="registro_hermano_id">
            </form>
        </div>
    </div>
</div>
