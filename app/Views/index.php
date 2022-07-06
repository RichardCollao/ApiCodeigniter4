<main>
    <div class="container mt-3">
        <h3>Indicadores economicos</h3>
        <hr />
        <form id="formulario_filtrar" action="#" method="post" onsubmit="apiFiltrar(event)">
            <div class="row justify-content-center">
                <div class="col-lg-3 col-sm-12">
                    <label class="form-label">Indicador</label>
                    <select name="codigo" class="form-select" data-selected="#">
                        <option value="all">all</option>
                        <?php foreach ($codigos as $key => $value) : ?>
                            <option value="<?php echo $value; ?>"><?php echo $value; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-3 col-sm-12">
                    <label class="form-label">Fecha inicial</label>
                    <input name="fecha_inicial" class="form-control" type="date" />
                </div>
                <div class="col-lg-3 col-sm-12">
                    <label class="form-label">Fecha final</label>
                    <input name="fecha_final" class="form-control" type="date" />
                </div>
                <div class="col-lg-2 col-sm-12">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="form-control btn btn-primary">Filtrar</button>
                </div>
            </div>
        </form>
        <div class="row justify-content-center mt-5">
            <table id="tabla" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Codigo</th>
                        <th>Nombre</th>
                        <th>Unidad medida</th>
                        <th>Fecha</th>
                        <th>Valor</th>
                        <th>opciones</th>
                    </tr>
                </thead>
                <tbody id="tbody">
                </tbody>
            </table>
        </div>
</main>

<template id="template_scraping">
    <div style="padding: 30px">
        <form id="formulario_scraping" action="#" method="post" onsubmit="apiScraping(event)">
            <div>
                <label class="form-label">Indicador:</label>
                <select class="form-select" name="code">
                    <?php
                    foreach ($data['codigos'] as $value) :
                        echo '<option value="' . $value . '">' . $value . '</option>';
                    endforeach;
                    ?>
                </select>
            </div>
            <div class="mt-3">
                <label class="form-label">Año:</label>
                <select class="form-select" name="year">
                    <?php
                    for ($i = 2022; $i > 2010; $i--) :
                        echo '<option value="' . $i . '">' . $i . '</option>';
                    endfor;
                    ?>
                </select>
            </div>
            <div class="mt-3 d-grid">
                <button type="submit" class="btn btn-primary">Actualizar</button>
            </div>
        </form>
    </div>
</template>

<template id="template_modify">
    <div style="padding: 30px">
        <form id="formulario_modify" action="#" method="post" onsubmit="apiModify(event)">
            <input type="hidden" class="form-control" name="id_indicator" value="" />
            <div class="mt-0">
                <label class="form-label">Codigo:</label>
                <input type="text" class="form-control" name="codigo" value="" disabled />
            </div>
            <div class="mt-3">
                <label class="form-label">Nombre:</label>
                <input type="text" class="form-control" name="nombre" value="" disabled />
            </div>
            <div class="mt-3">
                <label class="form-label">Unidad de medida:</label>
                <input type="text" class="form-control" name="unidad_medida" value="" disabled />
            </div>
            <div class="mt-3">
                <label class="form-label">Fecha:</label>
                <input type="text" class="form-control" name="fecha" value="" disabled />
            </div>
            <div class="mt-3">
                <label class="form-label">Valor:</label>
                <input type="text" class="form-control" name="valor" value="" />
            </div>
            <div class="mt-3 d-grid">
                <button type="submit" class="btn btn-primary">Modificar</button>
            </div>
        </form>
    </div>
</template>