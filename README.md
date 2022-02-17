# Mobbex for WP-Travel

## Requisitos
* PHP >= 5.6
* WordPress >= 5.0
* WP-Travel >= 5.0.0

## Instalación
> Asegurese de instalar y configurar WP-Travel antes de comenzar la integración.
1. Descargue la última versión del plugin desde https://github.com/mobbexco/wp-travel/releases.
2. Diríjase a la sección de plugins desde el panel de administración.
3. Presione el botón "Agregar nuevo" y seleccione el archivo comprimido.
4. Active el plugin y configure las credenciales desde el panel de métodos de pago de WP-Travel.

## Actualización
Cuando exista una actualización, esta se mostrará a modo de alerta en el listado de plugins. Para obtener la última versión sólo basta con hacer clic en ella.

## Configuración
El plugin cuenta con las siguientes opciones configurables:
<table>
<thead>
  <tr>
    <th>Nombre</th>
    <th>Descripción</th>
  </tr>
</thead>
<tbody>
  <tr>
    <td>Clave de API</td>
    <td>Credencial necesaria para procesar los pagos</td>
  </tr>
  <tr>
    <td>Token de acceso</td>
    <td>Credencial necesaria para procesar los pagos</td>
  </tr>
  <tr>
    <td>Modo de pruebas</td>
    <td>Permite realizar pagos con  <a href="https://mobbex.dev/tarjetas-de-prueba">tarjetas de pruebas</a></td>
  </tr>
  <tr>
</tbody>
</table>

## Desarrollo
El módulo utiliza componentes de [React](https://reactjs.org/) para mostrar el formulario de configuración.
Ejecute los siguientes comandos para construir un entorno capaz de compilar los archivos jsx:
> Antes de continuar, asegúrese de tener NPM y NodeJS instalados en su entorno.

1. Inicialice un proyecto NPM:
    ```
    npm init -y
    ```
2. Instale la librería [Babel](https://www.npmjs.com/package/@babel/cli) con el preset de React:
    ```
    npm install babel-cli@6 babel-preset-react-app@3
    ```
3. Ejecute la librería para que compile los archivos dinámicamente:
    ```
    npx babel --watch assets/js/src --out-dir assets/js --presets react-app/prod
    ```