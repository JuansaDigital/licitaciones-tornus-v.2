<?php

namespace App\Http\Controllers;

use App\Models\Licitacion;
use App\Services\MarketService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CalendarController extends Controller
{
    /**
     * Feed universal iCal (.ics) para suscripción en Google Calendar, Outlook o Apple Calendar
     */
    public function feed(Request $request, string $market = 'CO'): Response
    {
        $connection = strtolower($market) === 'pe' ? 'peru' : 'colombia';
        $marketName = strtolower($market) === 'pe' ? 'Perú' : 'Colombia';

        $licitaciones = Licitacion::on($connection)
            ->where('en_cartera', true)
            ->whereNotNull('fecha_cierre')
            ->where('fecha_cierre', '!=', '')
            ->get();

        $ics = "BEGIN:VCALENDAR\r\n";
        $ics .= "VERSION:2.0\r\n";
        $ics .= "PRODID:-//Tornus Agency//Procurement Calendar {$marketName}//ES\r\n";
        $ics .= "CALSCALE:GREGORIAN\r\n";
        $ics .= "METHOD:PUBLISH\r\n";
        $ics .= "X-WR-CALNAME:Licitaciones Tornus ({$marketName})\r\n";
        $ics .= "X-WR-TIMEZONE:America/Bogota\r\n";

        foreach ($licitaciones as $item) {
            try {
                $cierre = new \DateTime($item->fecha_cierre);
                $dtEnd = $cierre->format('Ymd\THis');
                // Estimamos 1 hora antes como inicio del evento de cierre
                $dtStart = (clone $cierre)->modify('-1 hour')->format('Ymd\THis');
                $dtStamp = now()->format('Ymd\THis\Z');

                $summary = addcslashes("CIERRE: " . ($item->referencia ?: $item->external_id) . " - " . mb_substr($item->objeto, 0, 60), "\n\r\t,;\\");
                $description = addcslashes("Entidad: {$item->entidad}\nPresupuesto: {$item->formatted_cuantia}\nEstado: {$item->cartera_estado}\nObjeto: {$item->objeto}\nEnlace: {$item->url_proceso}", "\n\r\t,;\\");
                $uid = "tornus-licitacion-{$item->id}-{$market}@tornus.agency";

                $ics .= "BEGIN:VEVENT\r\n";
                $ics .= "UID:{$uid}\r\n";
                $ics .= "DTSTAMP:{$dtStamp}\r\n";
                $ics .= "DTSTART:{$dtStart}\r\n";
                $ics .= "DTEND:{$dtEnd}\r\n";
                $ics .= "SUMMARY:{$summary}\r\n";
                $ics .= "DESCRIPTION:{$description}\r\n";
                if (!empty($item->url_proceso)) {
                    $ics .= "URL:{$item->url_proceso}\r\n";
                }

                // Alarma 1: T-48 horas (Verificación de garantías, firmas y pólizas)
                $ics .= "BEGIN:VALARM\r\n";
                $ics .= "ACTION:DISPLAY\r\n";
                $ics .= "DESCRIPTION:Alarma T-48h: Verificación final de pliegos y pólizas\r\n";
                $ics .= "TRIGGER:-PT48H\r\n";
                $ics .= "END:VALARM\r\n";

                // Alarma 2: T-3 horas (Subida al portal antes de congestión de servidores)
                $ics .= "BEGIN:VALARM\r\n";
                $ics .= "ACTION:DISPLAY\r\n";
                $ics .= "DESCRIPTION:Alarma T-3h: Cierre inminente. Subida obligatoria de oferta\r\n";
                $ics .= "TRIGGER:-PT3H\r\n";
                $ics .= "END:VALARM\r\n";

                $ics .= "END:VEVENT\r\n";
            } catch (\Exception $e) {
                continue;
            }
        }

        $ics .= "END:VCALENDAR\r\n";

        return response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"licitaciones_tornus_{$market}.ics\"",
            'Cache-Control' => 'no-cache, must-revalidate',
        ]);
    }
}

