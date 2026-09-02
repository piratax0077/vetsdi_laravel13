@extends('template.usuario.template')
@section('content')
    <div class="pcoded-main-container">
        <div class="pcoded-content">
            <!--Header-->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
<ul class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="{{ ROUTE('paciente.home') }}" title="Volver al inicio" aria-label="Volver al inicio"><i class="feather icon-home" aria-hidden="true"></i></a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="row ">
            <div class="col-12 text-center">
                 <h4 class="text-white f-24">Promociones especiales</h4>
                </div>
        </div>
                </div>
            </div>
            <!--Cierre: Header-->

        </div>
        
            <div class="row mt-n5 mx-1">
                <div class="col-sm-12 mt-n5">
                    <div class="card">
                        <div class="card-body">
                            <div class="form-row align-items-center">

                              <!-- Input group buscador -->
                              <div class="col-md-8">
                                <div class="input-group mb-2">
                                  
                                  <div class="input-group-prepend">
                                    <select class="custom-select" id="searchCategory" aria-label="Categor&iacute;a de b&uacute;squeda">
                                      <option value="" selected>Categor&iacute;a</option>
                                      <option value="todos">Ver todo</option>
                                      <option value="alimentos">Alimentos</option>
                                      <option value="farmacia">Medicamentos</option>
                                      <option value="peluqueria">Peluquer&iacute;a</option>
                                      <option value="hotel">Hotel</option>
                                      <option value="procedimiento">Procedimientos</option>
                                      <option value="servicios">Servicios</option>
                                      <option value="entretencion">Entretenci&oacute;n</option>
                                    </select>
                                  </div>

                                  <input 
                                    type="text" 
                                    class="form-control" 
                                    id="promoSearch"
                                    list="promoSearchSuggestions"
                                    autocomplete="off"
                                    placeholder="&iquest;Qu&eacute; est&aacute;s buscando?"
                                    aria-label="Buscar"
                                  >
                                  <datalist id="promoSearchSuggestions">
                                    <option value="Peluquer&iacute;a"></option>
                                    <option value="Alimento para perro"></option>
                                    <option value="Alimento para gato"></option>
                                    <option value="Medicamentos"></option>
                                    <option value="Hotel de mascotas"></option>
                                    <option value="Juguetes"></option>
                                    <option value="Servicios veterinarios"></option>
                                  </datalist>

                                  <div class="input-group-append">
                                    <button class="btn btn-purple" id="promoSearchButton" style="border-bottom-left-radius: 0px; border-top-left-radius: 0px;" type="button">
                                      <i class="feather icon-search"></i> Buscar
                                    </button>
                                  </div>

                                </div>
                              </div>

                              <!-- Select comuna Chile -->
                              <div class="col-md-4">
                                <select class="custom-select mb-2" id="comuna" aria-label="Ubicación de búsqueda">
                                  <option value="" @selected(empty($ubicacionUsuario))>¿Dónde te encuentras?</option>
                                  @foreach($comunas as $comuna)
                                    <option value="{{ $comuna }}" @selected($ubicacionUsuario === $comuna)>{{ $comuna }}</option>
                                  @endforeach
                                </select>
                                @if($ubicacionUsuario)
                                  <small class="form-text text-muted">Ubicación obtenida de tu dirección guardada.</small>
                                @endif
                              </div>

                            </div>
                            </div>
                        </div>
                    </div>
            </div>

            <div class="row mx-1 mb-3" id="promoSearchFeedback" aria-live="polite" style="display:none">
                <div class="col-12">
                    <div class="alert alert-info mb-0 d-flex flex-wrap align-items-center justify-content-between" style="gap:12px">
                        <span id="promoSearchMessage"></span>
                        <a id="promoServiceLink" class="btn btn-info btn-sm" href="{{ route('paciente.mascotas.suscripcion_servicios', ['servicio' => 'peluqueria']) }}" style="display:none">Buscar servicios cercanos</a>
                    </div>
                </div>
            </div>

            <div class="row mx-1 mb-4" id="promoSearchResults" style="display:none">
                <div class="col-12">
                    <div class="card border-0 shadow-sm mb-0">
                        <div class="card-header bg-white"><h5 class="mb-0">Resultados de la b&uacute;squeda</h5></div>
                        <div class="card-body">
                            <div class="row" id="promoSearchResultList"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mx-1">
                <div class="col-md-12">
                    <div id="carouselExampleFade" class="carousel slide carousel-fade shadow border-xl" data-ride="carousel">
                      <div class="carousel-inner">
                        <div class="carousel-item active">
                          <img src="{{ asset('images/promociones/slider1.jpg') }}" class="d-block w-100 rounded-xxl" alt="...">
                        </div>
                        <div class="carousel-item">
                          <img src="{{ asset('images/promociones/banner2.jpg') }}" class="d-block w-100 rounded-xxl" alt="...">
                        </div>
                      </div>
                      <!--<button class="carousel-control-prev" type="button" data-target="#carouselExampleFade" data-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="sr-only">Previous</span>
                      </button>
                      <button class="carousel-control-next" type="button" data-target="#carouselExampleFade" data-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="sr-only">Next</span>
                      </button>-->
                    </div>
                </div>
            </div>
            <div class="row mx-1">
                <div class="col-12 mt-5">
                    <h3>Cupones</h3>
                </div>
                <div class="col-sm-12 col-md-6 col-lg-3 col-xl-3">
                    <div class="card">
                      <img src="{{ asset('images/promociones/promo2.jpg') }}"  class="card-img-top" alt="...">
                      <div class="card-body text-center">
                        <button type="button" class="btn btn-outline-info btn-sm">Copiar cupón</button>
                        <a type="button" class="btn btn-info btn-outline-purple btn-sm" href="#" target="_blank">Ir al sitio</a>
                      </div>
                    </div>
                </div>
                <div class="col-sm-12 col-md-6 col-lg-3 col-xl-3">
                    <div class="card">
                      <img src="{{ asset('images/promociones/promo2.jpg') }}"  class="card-img-top" alt="...">
                      <div class="card-body text-center">
                        <button type="button" class="btn btn-outline-info btn-sm">Copiar cupón</button>
                        <a type="button" class="btn btn-info btn-outline-purple btn-sm" href="#" target="_blank">Ir al sitio</a>
                      </div>
                    </div>
                </div>
                <div class="col-sm-12 col-md-6 col-lg-3 col-xl-3">
                    <div class="card">
                      <img src="{{ asset('images/promociones/promo2.jpg') }}"  class="card-img-top" alt="...">
                      <div class="card-body text-center">
                        <button type="button" class="btn btn-outline-info btn-sm">Copiar cupón</button>
                        <a type="button" class="btn btn-info btn-outline-purple btn-sm" href="#" target="_blank">Ir al sitio</a>
                      </div>
                    </div>
                </div>
                <div class="col-sm-12 col-md-6 col-lg-3 col-xl-3">
                    <div class="card">
                      <img src="{{ asset('images/promociones/promo2.jpg') }}"  class="card-img-top" alt="...">
                      <div class="card-body text-center">
                        <button type="button" class="btn btn-outline-info btn-sm">Copiar cupón</button>
                        <a type="button" class="btn btn-info btn-outline-purple btn-sm" href="#" target="_blank">Ir al sitio</a>
                      </div>
                    </div>
                </div>
            </div>
            <div class="row mx-1">
                <div class="col-12 mt-4">
                    <h3>Promociones</h3>
                </div>
                <div class="col-sm-12 col-md-6 col-lg-3 col-xl-4">
                    <div class="card border-xl">
                      <img class="border-xl" src="{{ asset('images/promociones/promo1.jpg') }}"  alt="...">
                    </div>
                </div>
                <div class="col-sm-12 col-md-6 col-lg-3 col-xl-4">
                    <div class="card border-xl">
                      <img class="border-xl" src="{{ asset('images/promociones/promo1.jpg') }}"  alt="...">
                    </div>
                </div>
                <div class="col-sm-12 col-md-6 col-lg-3 col-xl-4">
                    <div class="card border-xl">
                      <img class="border-xl" src="{{ asset('images/promociones/promo1.jpg') }}"  alt="...">
                    </div>
                </div>
            </div>
             <div class="row mx-1 mt-4">
                <div class="col-12">
                    <h3>Ofertas</h3>
                </div>
                <div class="col-sm-12 col-md-6 col-lg-3 col-xl-3">
                    <div class="card">
                      <img src="{{ asset('images/promociones/venta1.jpg') }}"  class="card-img-top" alt="...">
                      <div class="card-body text-center">
                        <h6>Alimento para Pájaros Pretty Pets 500 g</h6>
                        <p><s>$5.990</s></p>
                        <h4>$990</h4>
                        <a type="button" class="btn btn-outline-purple btn-sm" href="#" target="_blank">Ir al descuento</a>
                      </div>
                    </div>
                </div>
                <div class="col-sm-12 col-md-6 col-lg-3 col-xl-3">
                    <div class="card">
                      <img src="{{ asset('images/promociones/venta1.jpg') }}"  class="card-img-top" alt="...">
                      <div class="card-body text-center">
                        <h6>Alimento para Pájaros Pretty Pets 500 g</h6>
                        <p><s>5.990</s></p>
                        <h4>$990</h4>
                       <a type="button" class="btn btn-outline-purple btn-sm" href="#" target="_blank">Ir al descuento</a>
                      </div>
                    </div>
                </div>
                <div class="col-sm-12 col-md-6 col-lg-3 col-xl-3">
                    <div class="card">
                      <img src="{{ asset('images/promociones/venta2.jpg') }}"  class="card-img-top" alt="...">
                      <div class="card-body text-center">
                        <h6>Juguete Para Gato Pájaro Volador Con Sonido</h6>
                        <p><s>15.990</s></p>
                        <h4>$5.990</h4>
                        <button type="button" class="btn btn-outline-purple btn-sm">Ir al descuento</button>
                      </div>
                    </div>
                </div>
                <div class="col-sm-12 col-md-6 col-lg-3 col-xl-3">
                    <div class="card">
                      <img src="{{ asset('images/promociones/venta3.jpg') }}"  class="card-img-top" alt="...">
                      <div class="card-body text-center">
                        <h6>Bravery Pork Mini Adult alimento para perro</h6>
                        <p><s>CLP 46,990</s></p>
                        <h4>CLP 22.990</h4>
                        <p><i>En Mercado Libre</i></p>
                        <button type="button" class="btn btn-outline-purple btn-sm">Ir al descuento</button>
                      </div>
                    </div>
                </div>
            </div>
        </div>


@endsection

@section('script-veneria')
<script>
  function formatComuna (comuna) {
    if (!comuna.id) {
      return '<i class="fas fa-map-marker-alt text-danger"></i> Seleccionar comuna';
    }
    return '<i class="fas fa-map-marker-alt text-primary"></i> ' + comuna.text;
  }

  $('#comuna').select2({
    placeholder: 'Seleccionar comuna',
    allowClear: true,
    width: '100%',
    templateResult: formatComuna,
    templateSelection: formatComuna,
    escapeMarkup: function (markup) { return markup; }
  });

  (function () {
    const searchInput = document.getElementById('promoSearch');
    const searchButton = document.getElementById('promoSearchButton');
    const categorySelect = document.getElementById('searchCategory');
    const communeSelect = document.getElementById('comuna');
    const feedback = document.getElementById('promoSearchFeedback');
    const message = document.getElementById('promoSearchMessage');
    const serviceLink = document.getElementById('promoServiceLink');
    const resultPanel = document.getElementById('promoSearchResults');
    const resultList = document.getElementById('promoSearchResultList');
    const carouselRow = document.getElementById('carouselExampleFade')?.closest('.row');
    const productSearchUrl = @json(route('paciente.mascotas.suscripcion_servicios.productos'));
    let timer;

    const normalize = (value) => (value || '')
      .toString()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .toLowerCase()
      .trim();

    const synonymGroups = [
      ['pelu', 'peluqueria', 'bano', 'higiene', 'corte'],
      ['alimento', 'comida', 'food', 'nutricion'],
      ['medicamento', 'farmacia', 'remedio'],
      ['hotel', 'hospedaje', 'guarderia'],
      ['juguete', 'entretencion', 'accesorio'],
      ['veterinario', 'consulta', 'procedimiento', 'salud']
    ];

    function expandedTerms(query) {
      const terms = normalize(query).split(/\s+/).filter(Boolean);
      const expanded = new Set(terms);
      terms.forEach((term) => {
        synonymGroups.forEach((group) => {
          if (group.some((word) => word.includes(term) || term.includes(word))) {
            group.forEach((word) => expanded.add(word));
          }
        });
      });
      return Array.from(expanded);
    }

    function searchableGroups() {
      return Array.from(document.querySelectorAll('.row.mx-1')).filter((row) => {
        const title = normalize(row.querySelector('h3')?.textContent);
        return ['cupones', 'promociones', 'ofertas'].includes(title);
      }).map((row) => ({
        row,
        title: normalize(row.querySelector('h3')?.textContent),
        cards: Array.from(row.querySelectorAll('.card')).map((card) => card.closest('[class*="col-"]')).filter(Boolean)
      }));
    }

    function categoryService() {
      const value = categorySelect.value;
      if (value === 'alimentos') return 'alimentos';
      if (value === 'farmacia') return 'farmacia';
      if (value === 'entretencion') return 'pet_shop';
      return '';
    }

    function selectedServiceResult() {
      const services = {
        peluqueria: ['Peluquer\u00eda y cuidado', 'Encuentra peluquer\u00edas, ba\u00f1o, corte e higiene para tu mascota.', 'peluqueria'],
        hotel: ['Hoteles y estad\u00edas', 'Consulta hoteles, guarder\u00edas y alternativas de alojamiento cercanas.', 'hotel'],
        procedimiento: ['Procedimientos veterinarios', 'Busca prestaciones y procedimientos disponibles para tu mascota.', 'procedimiento'],
        servicios: ['Servicios veterinarios', 'Consulta servicios y profesionales disponibles cerca de tu ubicaci\u00f3n.', 'servicios']
      };
      return services[categorySelect.value] || null;
    }

    function renderResults(items) {
      resultList.innerHTML = '';
      items.forEach((item) => {
        const column = document.createElement('div');
        column.className = 'col-sm-12 col-md-6 col-xl-4 mb-3';
        const card = document.createElement('article');
        card.className = 'card h-100 border';
        const body = document.createElement('div');
        body.className = 'card-body d-flex flex-column';
        const title = document.createElement('h6');
        title.className = 'font-weight-bold mb-2';
        title.textContent = item.name || item.label;
        const description = document.createElement('p');
        description.className = 'text-muted small mb-2';
        description.textContent = item.descripcion || item.source || 'Producto disponible';
        body.append(title, description);
        if (item.actionUrl) {
          const action = document.createElement('a');
          action.className = 'btn btn-info btn-sm mt-auto align-self-start';
          action.href = item.actionUrl;
          action.textContent = 'Ver opciones cercanas';
          body.appendChild(action);
        }
        if (item.price) {
          const price = document.createElement('strong');
          price.className = 'text-success mt-auto';
          price.textContent = new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP', maximumFractionDigits: 0 }).format(item.price);
          body.appendChild(price);
        }
        card.appendChild(body);
        column.appendChild(card);
        resultList.appendChild(column);
      });
      resultPanel.style.display = items.length ? '' : 'none';
    }

    async function runSearch() {
      const query = searchInput.value;
      const terms = expandedTerms(query);
      const selectedCategory = normalize(categorySelect.options[categorySelect.selectedIndex]?.text);
      const categoryIsAll = !categorySelect.value || categorySelect.value === 'todos';
      const commune = communeSelect.value || communeSelect.options[communeSelect.selectedIndex]?.text || '';
      let visible = 0;

      const groups = searchableGroups();
      groups.forEach((group) => {
        let groupVisible = 0;
        group.cards.forEach((column) => {
          const haystack = normalize(`${group.title} ${column.textContent}`);
          const matchesText = terms.length === 0 || terms.some((term) => haystack.includes(term));
          const matchesCategory = categoryIsAll || haystack.includes(selectedCategory) || terms.some((term) => selectedCategory.includes(term));
          const show = matchesText && matchesCategory;
          column.style.display = show ? '' : 'none';
          if (show) { visible += 1; groupVisible += 1; }
        });
        group.row.style.display = groupVisible || (terms.length === 0 && categoryIsAll) ? '' : 'none';
      });

      const active = terms.length > 0 || !categoryIsAll;
      feedback.style.display = active ? '' : 'none';
      serviceLink.style.display = 'none';
      if (carouselRow) carouselRow.style.display = active ? 'none' : '';
      if (!active) {
        renderResults([]);
        return;
      }

      message.textContent = 'Buscando coincidencias disponibles...';

      let remoteItems = [];
      const serviceResult = selectedServiceResult();
      if (serviceResult) {
        remoteItems = [{
          name: serviceResult[0],
          descripcion: serviceResult[1],
          actionUrl: @json(route('paciente.mascotas.suscripcion_servicios')) + '?servicio=' + encodeURIComponent(serviceResult[2])
        }];
      } else {
        try {
          const params = new URLSearchParams({ search: query, service: categoryService() });
          const response = await fetch(`${productSearchUrl}?${params.toString()}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
          });
          if (!response.ok) throw new Error('No fue posible consultar el catálogo');
          remoteItems = await response.json();
          if (!Array.isArray(remoteItems)) remoteItems = [];
        } catch (error) {
          remoteItems = [];
        }
      }

      renderResults(remoteItems);
      visible += remoteItems.length;

      const place = commune && normalize(commune) !== 'donde te encuentras' ? ` en ${commune}` : '';
      if (visible > 0) {
        message.textContent = `${visible} coincidencia${visible === 1 ? '' : 's'} encontrada${visible === 1 ? '' : 's'}${place}.`;
      } else {
        message.textContent = `No hay promociones publicadas para “${query || categorySelect.options[categorySelect.selectedIndex].text}”${place}.`;
        const serviceTerms = normalize(`${query} ${selectedCategory}`);
        if (['pelu', 'peluqueria', 'hotel', 'veterinario', 'servicio'].some((term) => serviceTerms.includes(term))) {
          serviceLink.style.display = '';
        }
      }
    }

    searchInput.addEventListener('input', () => {
      clearTimeout(timer);
      timer = setTimeout(runSearch, 180);
    });
    searchInput.addEventListener('keydown', (event) => {
      if (event.key === 'Enter') { event.preventDefault(); runSearch(); }
    });
    searchButton.addEventListener('click', runSearch);
    categorySelect.addEventListener('change', runSearch);
    $('#comuna').on('change', runSearch);
  })();
</script>
@endsection
