window.addEventListener("load", function (event) {
  // Inicializa el modal
  modal = new bootstrap.Modal(
    document.getElementById('exampleModal'), {
    keyboard: false
  });
  // Asigna evento al enlace actualizar 
  document.querySelector('#scraping').onclick = function () {
    // Update the modal's content.
    var modalTitle = exampleModal.querySelector('.modal-title');
    var modalBody = exampleModal.querySelector('.modal-body');
    modalTitle.textContent = 'Actualizar desde: https://mindicador.cl/api';
    modalBody.id = "dynamic_content";
    setDynamicContent("template_scraping");
    modal.show();
  }
  // Llama la funcion responsable de poblar la tabla con datos por defecto
  start();
}, false);

function requestApi(uri, method, callback, formData = {}) {
  // this.showLoading();
  var headers = new Headers();

  var options = {
    method: method,
    headers: headers,
    mode: 'same-origin', // https://developer.mozilla.org/en-US/docs/Web/API/Request/mode
    credentials: 'include',
    cache: 'default'
  }
  if (method == 'POST') {
    options.body = formData;
  }

  var request = new Request(uri, options);
  fetch(request).then(function (response) {
    if (response.ok) {
      // Llama a la funcion callback y pasa el resultado como argumento
      response.text().then(
        function (response) {
          // console.log(response);
          try {
            // Parsea los datos recibidos en un objeto json y llama la funcion callback
            var json = JSON.parse(response);
            callback(json);
          } catch (e) {
            callback({ "errors": ['La respuesta del servidor no es de tipo Json'] });
            console.log('error lvl1: ' + e);
          }
        });
    } else {
      console.log('error lvl2' + response.status);
    }
  }).catch(function (err) {
    console.log('error lvl3', err);
  });
}

function setDynamicContent(id_template) {
  var dynamic_content = document.querySelector('#dynamic_content');
  var template = document.getElementById(id_template);
  dynamic_content.innerHTML = '';
  var nodeClone = document.importNode(template.content, true);
  dynamic_content.appendChild(nodeClone);
}

// Inicializa el toast
function toast(str) {
  var myToastEl = document.querySelector('.toast');
  var myToast = bootstrap.Toast.getOrCreateInstance(myToastEl);
  myToastEl.querySelector('.toast-body').innerHTML = str;
  myToast.show();
}

function start() {
  callback = function (json) {
    renderTbody(json);
  }
  // Envia la peticion al servidor
  requestApi("/api", "GET", callback, {});
  return false;
}

function apiScraping(event) {
  event.preventDefault();
  callback = function (json) {
    toast("Datos actualizados");
    start();
  }
  modal.hide();
  // Envia la peticion al servidor
  var formData = new FormData(document.querySelector('#formulario_scraping'));
  requestApi('/api/scraping', "POST", callback, formData);
  return false;
}

function apiFiltrar(event) {
  event.preventDefault();
  callback = function (json) {
    renderTbody(json);
    toast("Datos actualizados");
  }
  // Envia la peticion al servidor
  var formData = new FormData(document.querySelector('#formulario_filtrar'));
  requestApi("/api/filter", "POST", callback, formData);
  return false;
}

function displayFormModify(obj) {
  // Update the modal's content.
  var modalTitle = exampleModal.querySelector('.modal-title');
  var modalBody = exampleModal.querySelector('.modal-body');
  var btnSubmit = exampleModal.querySelector('.modal-body');
  modalTitle.textContent = 'Modificar:';
  modalBody.id = "dynamic_content";
  setDynamicContent("template_modify");
  var form = document.querySelector('#formulario_modify');
  form.querySelector('input[name="id_indicator"]').value = obj.id_indicator;
  form.querySelector('input[name="codigo"]').value = obj.codigo;
  form.querySelector('input[name="nombre"]').value = obj.nombre;
  form.querySelector('input[name="unidad_medida"]').value = obj.unidad_medida;
  form.querySelector('input[name="fecha"]').value = obj.fecha;
  form.querySelector('input[name="valor"]').value = obj.valor;
  modal.show();
}

function apiModify(event) {
  event.preventDefault();
  callback = function (json) {
    toast("Datos actualizados");
    start();
  }
  modal.hide();
  // Envia la peticion al servidor
  var formData = new FormData(document.querySelector('#formulario_modify'));
  requestApi("/api/modify", "POST", callback, formData);
  return false;
}

function apiEliminar(id) {
  callback = function (json) {
    toast("Datos actualizados");
    start();
  }
  // Envia la peticion al servidor
  requestApi("/api/" + id, "DELETE", callback, {});
  return false;
}

function renderTbody(json) {
  // Elimina el datatable
  $('#tabla').DataTable().destroy();
  tbody = document.querySelector('#tbody');
  tbody.innerHTML = '';
  json.data.forEach(function (indicador) {
    var row = tbody.insertRow(0);
    var cell1 = row.insertCell(0);
    var cell2 = row.insertCell(1);
    var cell3 = row.insertCell(2);
    var cell4 = row.insertCell(3);
    var cell5 = row.insertCell(4);
    var cell6 = row.insertCell(5);
    cell1.innerHTML = indicador.codigo;
    cell2.innerHTML = indicador.nombre;
    cell3.innerHTML = indicador.unidad_medida;
    cell4.innerHTML = indicador.fecha;
    cell5.innerHTML = indicador.valor;
    var btnEditar = document.createElement("BUTTON");
    var btnEliminar = document.createElement("BUTTON");

    btnEditar.addEventListener("click", displayFormModify.bind(this, indicador));
    btnEliminar.addEventListener("click", apiEliminar.bind(this, indicador.id_indicator));

    btnEditar.className = "btn btn-primary btn-sm";
    btnEliminar.className = "btn btn-danger btn-sm";
    btnEditar.innerHTML = "Editar";
    btnEditar.style.cssText = "margin-right: 15px;";
    btnEliminar.innerHTML = "Eliminar";
    cell6.style.cssText = "text-align: right;";
    cell6.appendChild(btnEditar);
    cell6.appendChild(btnEliminar);
  });
  // crea un nuevo datable
  $('#tabla').DataTable();// $('#tabla').DataTable().draw();
}

// scope this script
var modal;