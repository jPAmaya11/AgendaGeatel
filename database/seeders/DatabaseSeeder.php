<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Cartera;
use App\Models\Reporte;
use Illuminate\Database\Seeder;
use App\Models\Role;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\PruebaSeeder;


class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1 Seeders fijos
        $this->call([
            PermissionSeeder::class,
            CompaniaSeeder::class,
            EstadoSeeder::class,
            UsuarioSeeder::class, // si lo tienes, o cualquier otro fijo
        ]);

        // 2 Factories dependientes
        // Primero los chips (porque dependen de companias)
        \App\Models\Chip::factory(20)->create();

        // Luego los registros (porque dependen de chips)
        \App\Models\Registro::factory(50)->create();



        // 1. Seed inicial de roles y permisos
        $this->call([
            PermissionSeeder::class,
        ]);

        // 2. Crear usuarios
        $admin = User::factory()->create([
            'name' => 'Geatel',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('test'),
        ]);

        $fernando = User::factory()->create([
            'name' => 'Fernando',
            'email' => 'fernandoxl01xs@gmail.com',
            'password' => bcrypt('ignxts'),
        ]);

        $webie = User::factory()->create([
            'name' => 'Webie',
            'email' => 'webie@gmail.com',
            'password' => bcrypt('test'),
        ]);


        // 3. Asignar roles a usuarios
        $rolAdmin = Role::where('name', 'admin')->first();
        $rolWebie = Role::where('name', 'asesor')->first();

        if ($rolAdmin) {
            $admin->assignRole($rolAdmin);
            $fernando->assignRole($rolAdmin);
        }

        if ($rolWebie) {
            $webie->assignRole($rolWebie);
        }

        // 4. Crear carteras
        $carterasData = [
            ['id' => 3,  'nombre' => 'Win',           'descripcion' => 'Cartera Servicios WIN',        'orden' => 1],
            ['id' => 29, 'nombre' => 'Win CROSS',     'descripcion' => 'Cartera Servicios WIN',        'orden' => 2],
            ['id' => 4,  'nombre' => 'PerúFibra',     'descripcion' => 'Cartera Servicios PerúFibra',  'orden' => 3],
            ['id' => 28, 'nombre' => 'Cable Perú',    'descripcion' => 'Cartera Servicios CP',         'orden' => 4],
            ['id' => 1,  'nombre' => 'Telefonía',     'descripcion' => 'Cartera Telefonía España',     'orden' => 5],
            ['id' => 2,  'nombre' => 'Energía',       'descripcion' => 'Cartera Energía España',       'orden' => 6],
            ['id' => 27, 'nombre' => 'Fidelización',  'descripcion' => 'Seguimiento Fidelización Energía', 'orden' => 7],
            ['id' => 30, 'nombre' => 'Infinite',      'descripcion' => 'Agencia de Viajes',            'orden' => 8],
        ];

        $carteras = [];
        foreach ($carterasData as $data) {
            $carteras[$data['nombre']] = Cartera::updateOrCreate(['id' => $data['id']], $data);
        }
        $indicadoricon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><!--! Font Awesome Pro 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2024 Fonticons, Inc. --><path d="M384 64L64 64C46.3 64 32 78.3 32 96l0 320c0 17.7 14.3 32 32 32l250.4 0c6.6 11.4 14.4 22.2 23.2 32L64 480c-35.3 0-64-28.7-64-64L0 96C0 60.7 28.7 32 64 32l320 0c35.3 0 64 28.7 64 64l0 32.2c-11.1-1-22.6 1.9-32 8.8l0-41c0-17.7-14.3-32-32-32zM327.2 239.5c-2.3 .4-4.7 .5-7.2 .5l-192 0c-26.5 0-48-21.5-48-48l0-32c0-26.5 21.5-48 48-48l192 0c26.5 0 48 21.5 48 48l0 24.9c-15.5 17.9-29.3 36.3-40.8 54.5zM112 280a24 24 0 1 1 0 48 24 24 0 1 1 0-48zm96 0a24 24 0 1 1 0 48 24 24 0 1 1 0-48zM112 160l0 32c0 8.8 7.2 16 16 16l192 0c8.8 0 16-7.2 16-16l0-32c0-8.8-7.2-16-16-16l-192 0c-8.8 0-16 7.2-16 16zm335.1 32.9c-21.7 19.8-45.3 44.8-63.7 72.3c-19 28.5-31.4 58.3-31.4 86.7c0 33.7 12.7 64.2 33.6 87c-1.1-5.4-1.6-10.9-1.6-16.6c0-57.4 63.8-110.5 87.4-128.2c5.2-3.9 12.1-3.9 17.3 0C512.2 311.9 576 365 576 422.4c0 5.5-.5 10.9-1.6 16.2C595.3 415.9 608 385.5 608 352c0-22.6-8.5-48-22.2-72.6c-13-23.4-30.1-44.9-46.7-60.7l-21 23.7c-3 3.3-7.2 5.3-11.7 5.4s-8.8-1.7-11.9-5l-47.4-49.8zM480 479.9c2.4 0 4.8-.1 7.1-.2c33.7-3.3 56.9-29.4 56.9-57.3c0-17.4-10.1-37.7-27.4-58.5c-12.1-14.5-25.8-27-36.6-35.8c-10.7 8.8-24.5 21.3-36.6 35.8C426.1 384.7 416 405 416 422.4c0 27.9 23.3 54 57 57.3c2.3 .1 4.7 .2 7 .2zM320 352c0-37.3 16-73.4 36.8-104.5c20.9-31.3 47.5-59 70.9-80.2c11.1-10.1 28.2-10 39.3 .1c.3 .3 .6 .5 .8 .8l37.9 39.9L518 194.3c.4-.4 .7-.8 1.1-1.2c11.2-10.3 28.5-10.3 39.7 .1c19.7 18.3 39.8 43.2 55 70.6C629 291.1 640 322 640 352c0 85.6-66.4 154.9-150.8 159.6c-3 .3-6.1 .4-9.2 .4c-3.1 0-6.1-.1-9.1-.4C385.7 507 320 437.6 320 352z"/></svg>';
        $conexionicon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><!--! Font Awesome Pro 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2024 Fonticons, Inc. --><path d="M591.5 256c50-50 50-131 0-181s-131-50-181 0L387.9 97.6c-6.2 6.2-6.2 16.4 0 22.6s16.4 6.2 22.6 0l22.6-22.6c37.5-37.5 98.3-37.5 135.8 0s37.5 98.3 0 135.8L444.3 357.9c-37.4 37.4-98.1 37.4-135.6 0c-35.6-35.6-37.6-92.6-4.7-130.6l5.3-6.1c5.8-6.7 5.1-16.8-1.6-22.6s-16.8-5.1-22.6 1.6l-5.3 6.1c-43.9 50.7-41.2 126.7 6.2 174.1c49.9 49.9 130.9 49.9 180.8 0L591.5 256zM48.5 256c-50 50-50 131 0 181s131 50 181 0l22.6-22.6c6.2-6.2 6.2-16.4 0-22.6s-16.4-6.2-22.6 0l-22.6 22.6c-37.5 37.5-98.3 37.5-135.8 0s-37.5-98.3 0-135.8L195.7 154.1c37.4-37.4 98.1-37.4 135.6 0c35.6 35.6 37.6 92.6 4.7 130.6l-5.3 6.1c-5.8 6.7-5.1 16.8 1.6 22.6s16.8 5.1 22.6-1.6l5.3-6.1c43.9-50.7 41.2-126.7-6.2-174.1C303.9 81.5 223 81.5 173 131.4L48.5 256z"/></svg>';
        $ventasicon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--! Font Awesome Pro 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2024 Fonticons, Inc. --><path d="M64 80c-8.8 0-16 7.2-16 16l0 320c0 8.8 7.2 16 16 16l320 0c8.8 0 16-7.2 16-16l0-320c0-8.8-7.2-16-16-16L64 80zM0 96C0 60.7 28.7 32 64 32l320 0c35.3 0 64 28.7 64 64l0 320c0 35.3-28.7 64-64 64L64 480c-35.3 0-64-28.7-64-64L0 96zm244.8 37.6l0 14.2c9.7 1.2 19.4 3.9 29 6.6c1.9 .5 3.7 1 5.6 1.6c11.5 3.2 18.3 15.1 15.1 26.6s-15.1 18.2-26.6 15.1c-1.6-.4-3.1-.9-4.7-1.3c-7-2-14-3.9-21.1-5.3c-13.2-2.5-28.5-1.3-40.8 4c-11 4.8-20.1 16.4-7.6 24.4c9.8 6.3 21.8 9.5 33.2 12.6c2.4 .6 4.7 1.3 7 1.9c15.6 4.4 35.5 10.1 50.4 20.3c19.4 13.3 28.5 34.9 24.2 58.1c-4.1 22.4-19.7 37.1-38.4 44.7c-7.8 3.2-16.3 5.2-25.2 6.2l0 15.2c0 11.9-9.7 21.6-21.6 21.6s-21.6-9.7-21.6-21.6l0-17.4c-14.5-3.3-28.7-7.9-42.8-12.5c-11.3-3.7-17.5-16-13.7-27.3s16-17.5 27.3-13.7c2.5 .8 5 1.7 7.5 2.5c11.3 3.8 22.9 7.7 34.5 9.6c17 2.5 30.6 1 39.5-2.6c12-4.8 17.7-19.1 5.9-27.1c-10.1-6.9-22.6-10.3-34.5-13.5c-2.3-.6-4.5-1.2-6.8-1.9c-15.1-4.3-34-9.6-48.2-18.7c-19.5-12.5-29.4-33.3-25.2-56.4c4-21.8 21-36.3 39-44.1c5.5-2.4 11.4-4.3 17.5-5.7l0-16.1c0-11.9 9.7-21.6 21.6-21.6s21.6 9.7 21.6 21.6z"/></svg>';
        $reportesData = [
            ['cartera' => 'Energía',   'icon' => $conexionicon, 'nombre' => 'Conexion_Agentes', 'link_desktop' => 'https://app.powerbi.com/view?r=eyJrIjoiYTUzNTdiNzEtYjQ3OS00ZGZlLWI5YjktODFiNzU1NDBkZmQ4IiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 3],
            ['cartera' => 'Telefonía', 'icon' =>  $conexionicon, 'nombre' => 'Conexion_Agentes', 'link_desktop' => 'https://app.powerbi.com/view?r=eyJrIjoiMGY1YjA0MjktN2JmNC00MTM5LWIzM2ItZDdmZmM2ZWE5Mjc0IiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 3],
            ['cartera' => 'Telefonía', 'icon' =>   $indicadoricon,  'nombre' => 'Indicadores por Día', 'link_desktop' => 'https://app.powerbi.com/view?r=eyJrIjoiN2JiMDYxMDktZmU4NS00NmYyLTk1OTAtMmE1NmE0YTkwNjI1IiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 1],
            ['cartera' => 'Win', 'icon' =>   $indicadoricon,    'nombre' => 'Indicadores por Día', 'link_desktop' => 'https://app.powerbi.com/view?r=eyJrIjoiMmIyZmQ5MzEtMDIxOS00NjgxLTliOWMtMWQ1ZTI5YjExZThhIiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 1],
            ['cartera' => 'Win',   'icon' =>  $conexionicon,     'nombre' => 'Conexion_Agentes', 'link_desktop' => 'https://app.powerbi.com/view?r=eyJrIjoiNmIzY2FkNGQtNDA5OC00NTM1LTk5NDItOTRiNWM1OWQyYzE0IiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 7],
            ['cartera' => 'Energía', 'icon' =>   $indicadoricon,   'nombre' => 'Indicadores por Día', 'link_desktop' => 'https://app.powerbi.com/view?r=eyJrIjoiYjI4YjU5ODMtM2RkYy00OTU3LTg3MWMtMjdjNDc0YzU5OTA0IiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 1],
            ['cartera' => 'Telefonía',    'icon' =>   $ventasicon, 'nombre' => 'Seguimiento Ventas', 'link_desktop' => 'https://app.powerbi.com/view?r=eyJrIjoiZGYwZmJjZWMtOTUxNi00Y2Y3LThkM2QtZGI0MmY5ODZjYjg0IiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 2],
            ['cartera' => 'Energía',      'icon' =>   $ventasicon, 'nombre' => 'Seguimiento Ventas', 'link_desktop' => 'https://app.powerbi.com/view?r=eyJrIjoiZTcxNmViYTYtN2Q5Zi00NTFiLTk1YjItOWRlYTczOGI0MDI5IiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 2],
            ['cartera' => 'PerúFibra',  'icon' =>   $indicadoricon, 'nombre' => 'Indicadores_BD', 'link_desktop' => 'https://app.powerbi.com/view?r=eyJrIjoiMWNiMGE1Y2QtNjY2OC00MGQwLTlkMWMtNzEwZmY4ZjNhZmNmIiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 4],
            ['cartera' => 'PerúFibra', 'icon' =>  $conexionicon, 'nombre' => 'Perú_Fibra_Día', 'link_desktop' => 'https://app.powerbi.com/view?r=eyJrIjoiN2Y0NjY0M2ItZTc0OC00MGQ1LWJjODAtMGZjZWRhNGM2YjBiIiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 1],
            ['cartera' => 'Fidelización', 'icon' =>  $conexionicon, 'nombre' => 'Conexion_Agentes', 'link_desktop' => 'https://app.powerbi.com/view?r=eyJrIjoiNzc0M2MyZTMtOWUxNS00OTM1LWE2MDgtYjU0MzljODJhYzBhIiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 1],
            ['cartera' => 'Cable Perú', 'icon' =>   $indicadoricon, 'nombre' => 'Indicadores_BD', 'link_desktop' => 'https://app.powerbi.com/view?r=eyJrIjoiYjEyNzJlYTItOTAwZi00NWM2LTg3ZGUtNWQ4MzMzNDE4ZDI4IiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 1],
            ['cartera' => 'Cable Perú',   'icon' =>   $ventasicon,  'nombre' => 'Seguimiento', 'link_desktop' => 'https://app.powerbi.com/view?r=eyJrIjoiZTBhOWM5YTQtY2UyMi00NDY4LTk1OTYtNWIyMzAwNDM3NjI2IiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 2],
            ['cartera' => 'PerúFibra', 'icon' =>  $conexionicon, 'nombre' => 'Conexion_Agentes', 'link_desktop' => 'https://app.powerbi.com/view?r=eyJrIjoiNjliM2RkNzgtMDA1Ni00ZjNmLTg2YTQtZDNjYWIxNGM4M2E2IiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 3],
            ['cartera' => 'PerúFibra',   'icon' =>   $ventasicon,  'nombre' => 'Seguimiento', 'link_desktop' => 'https://app.powerbi.com/view?r=eyJrIjoiNmI2NjRiMjQtZmRiMC00MmU2LTgwNjctMjNmNDhmODVhNzdmIiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 2],
            ['cartera' => 'Win',     'icon' =>   $indicadoricon,   'nombre' => 'Indicadores_BD_CROSS_Dia', 'link_desktop' => 'https://app.powerbi.com/view?r=eyJrIjoiZWQ3ZWZkNzgtNzI3My00MWU1LWE0NDYtMjg4MGQ0MGZlNGU3IiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 3],
            ['cartera' => 'Cable Perú', 'icon' =>  $conexionicon, 'nombre' => 'Conexion_Agentes', 'link_desktop' => 'https://app.powerbi.com/view?r=eyJrIjoiNjU0YWI3NjAtOTEzOS00ODQyLThiODItOGRhZGQyMTc4ZmE1IiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 3],
            ['cartera' => 'Energía', 'icon' =>   $indicadoricon,   'nombre' => 'Indicadores BD Histórico', 'link_desktop' => 'https://app.powerbi.com/view?r=eyJrIjoiZTg1YmMxYzQtOTNmZC00YzVmLThmMDktMzFlY2M3YjAxNjM4IiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 5],
            ['cartera' => 'Energía',  'icon' =>   $indicadoricon,  'nombre' => 'Indicadores_BD_Día', 'link_desktop' => 'https://app.powerbi.com/view?r=eyJrIjoiOGEwMzFiZmItNjY2Zi00ODRlLWJhNzAtNDI2MWMwMmI0NjU0IiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 4],
            ['cartera' => 'Win',     'icon' =>   $indicadoricon,   'nombre' => 'Indicadores_BD_CROSS_Historico', 'link_desktop' => 'https://app.powerbi.com/view?r=eyJrIjoiZjEzMzNjYmEtNDA0YS00ZjJlLWFiODctOWYwMjMzOWRlMGFmIiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 3],
            ['cartera' => 'Win',     'icon' =>   $indicadoricon,   'nombre' => 'Indicadores_BD_OUT_Dia', 'link_desktop' => 'https://app.powerbi.com/view?r=eyJrIjoiMThlOTgxYWEtMDAwNy00ZjQyLTgwOTAtMjMyOWFiZmQzZjUxIiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 4],
            ['cartera' => 'Win',    'icon' =>   $indicadoricon,    'nombre' => 'Indicadores_BD_OUT_Historico', 'link_desktop' => 'https://app.powerbi.com/view?r=eyJrIjoiMWM2ZjQ1OTMtYTk3Mi00NzMyLWJjZmUtOGQ5NjhhYTY0ZTIyIiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 5],
            ['cartera' => 'Win',   'icon' =>   $ventasicon,     'nombre' => 'Seguimiento Ventas', 'link_desktop' => 'https://app.powerbi.com/view?r=eyJrIjoiNmRkNDk4MmItMWY5Zi00Mjk0LWExNjUtZmJlNTA2MGQwZGIyIiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 2],
            ['cartera' => 'Infinite',  'icon' =>   $indicadoricon, 'nombre' => 'Indicadores BD del Día', 'link_desktop' => 'https://app.powerbi.com/view?r=eyJrIjoiYTY2MGUwNmQtYzMxZC00MDRmLTg5ZjctYTE4NDMzNjllNzM4IiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 2],
            ['cartera' => 'Infinite',  'icon' =>   $indicadoricon, 'nombre' => 'Indicadores_BD_Acumulado', 'link_desktop' => 'https://app.powerbi.com/view?r=eyJrIjoiNWZjNDdhNmItZDMxNS00MTk2LWJjOTgtMDIxZTc0YTJlMjU2IiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 3],
            ['cartera' => 'Infinite',  'icon' =>   $indicadoricon, 'nombre' => 'Indicadores de Venta en el Día', 'link_desktop' => 'https://app.powerbi.com/view?r=eyJrIjoiZWRjNzQ2YmYtYjBiZC00OWM2LWIwMWItNjk5NjljNGVlNTNjIiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 1],
            ['cartera' => 'Telefonía', 'icon' =>   $indicadoricon, 'nombre' => 'BD_Indicadores_Mes', 'link_desktop' => 'https://app.powerbi.com/view?r=eyJrIjoiYmNjMjViOTktNGY0OC00ZTUxLWJhMGUtZDNiMWU1NGU0MTVhIiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 4],
        ];

        foreach ($reportesData as $data) {
            if (isset($carteras[$data['cartera']])) {
                \App\Models\Reporte::create([
                    'nombre'      => $data['nombre'],
                    'link_desktop'        => $data['link_desktop'],
                    'orden'       => $data['orden'],
                    'cartera_id'  => $carteras[$data['cartera']]->id,
                    'icon'        => $data['icon'],
                ]);
            }
        }


        // 6. Asignar carteras y reportes a los roles
        $allCarteras = Cartera::pluck('id')->toArray();
        $allReportes = Reporte::pluck('id')->toArray();

        if ($rolAdmin) {
            $rolAdmin->carteras()->sync($allCarteras);
            $rolAdmin->reportes()->sync($allReportes);
        }

        if ($rolWebie) {
            $rolWebie->carteras()->sync($allCarteras);
            $rolWebie->reportes()->sync($allReportes);
        }
        
        // 7. Seeder de prueba (opcional)

    }
}
