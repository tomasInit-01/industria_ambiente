<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Provincia;
use App\Models\Municipio;
use App\Models\Localidad;

class ImportarDatosGeograficos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:importar-datos-geograficos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->importarProvincias();
        $this->importarMunicipios();
        $this->importarLocalidades();
    
        $this->info("¡Datos geográficos importados correctamente!");
    }
    
    protected function importarProvincias()
    {
        $path = storage_path('app/data/provincias.json');
        $contenido = file_get_contents($path);
        if (!$contenido) {
            $this->error('No se pudo leer el archivo provincias.json');
            return;
        }
    
        $json = json_decode($contenido, true);
        if (is_null($json)) {
            $this->error('Error decodificando JSON: ' . json_last_error_msg());
            return;
        }
    
        foreach ($json['provincias'] as $prov) {
            Provincia::updateOrCreate(
                ['codigo' => $prov['id']],
                ['nombre' => $prov['nombre']]
            );
        }
    
        $this->info('Provincias importadas correctamente');
    }
    
    protected function importarMunicipios()
{
    $path = storage_path('app/data/municipios.json');
    $contenido = file_get_contents($path);
    if (!$contenido) {
        $this->error('No se pudo leer el archivo municipios.json');
        return;
    }

    $json = json_decode($contenido, true);
    if (is_null($json)) {
        $this->error('Error decodificando JSON municipios.json: ' . json_last_error_msg());
        return;
    }

    foreach ($json['municipios'] as $muni) {
        $provincia = Provincia::where('codigo', $muni['provincia']['id'])->first();
        if (!$provincia) continue;

        Municipio::updateOrCreate(
            ['codigo' => $muni['id']],
            [
                'nombre' => $muni['nombre'],
                'categoria' => $muni['categoria'] ?? null,
                'centroide' => $muni['centroide'] ?? null,
                'provincia_id' => $provincia->id,
            ]
        );
    }

    $this->info('Municipios importados correctamente');
}

protected function importarLocalidades()
{
    $path = storage_path('app/data/localidades.json');
    $contenido = file_get_contents($path);
    if (!$contenido) {
        $this->error('No se pudo leer el archivo localidades.json');
        return;
    }

    $json = json_decode($contenido, true);
    if (is_null($json)) {
        $this->error('Error decodificando JSON localidades.json: ' . json_last_error_msg());
        return;
    }

    foreach ($json['localidades'] as $loc) {
        $provincia = Provincia::where('codigo', $loc['provincia']['id'])->first();
        $municipio = Municipio::where('codigo', $loc['municipio']['id'] ?? null)->first();

        if (!$provincia) continue;

        Localidad::updateOrCreate(
            ['codigo' => $loc['id']],
            [
                'nombre' => $loc['nombre'],
                'categoria' => $loc['categoria'] ?? null,
                'centroide' => $loc['centroide'] ?? null,
                'provincia_id' => $provincia->id,
                'municipio_id' => $municipio?->id,
            ]
        );
    }

    $this->info('Localidades importadas correctamente');
}


}
