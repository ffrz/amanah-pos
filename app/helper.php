<?php

/**
 * Proprietary Software / Perangkat Lunak Proprietary
 * Copyright (c) 2025 Fahmi Fauzi Rahman. All rights reserved.
 * 
 * EN: Unauthorized use, copying, modification, or distribution is prohibited.
 * ID: Penggunaan, penyalinan, modifikasi, atau distribusi tanpa izin dilarang.
 * 
 * See the LICENSE file in the project root for full license information.
 * Lihat file LICENSE di root proyek untuk informasi lisensi lengkap.
 * 
 * GitHub: https://github.com/ffrz
 * Email: fahmifauzirahman@gmail.com
 */

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


function encrypt_id($string)
{
    return base64_encode($string);
}

function decrypt_id($string)
{
    return base64_decode($string);
}

function wa_me_url($phone, $message = '')
{
    if (empty($phone) || strlen($phone) > 15) {
        return '#';
    }

    $phone = str_replace(' ', '', $phone);
    $phone = str_replace('-', '', $phone);
    if (substr($phone, 0, 1) == 0) {
        $phone = '62' . substr($phone, 1, strlen($phone));
    }
    return 'http://wa.me/' . $phone . '?text=' . $message;
}

function datetime_range_today()
{
    return [date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')];
}

function datetime_range_yesterday()
{
    $datetime = strtotime("-1 days");
    return [date('Y-m-d 00:00:00', $datetime), date('Y-m-d 23:59:59', $datetime)];
}

function datetime_range_this_week()
{
    $start = strtotime('next Monday -1 week');
    $start = date('w', $start) == date('w') ? strtotime(date("Y-m-d", $start) . " +7 days") : $start;
    $end = strtotime(date("Y-m-d", $start) . " +6 days");
    return [date('Y-m-d 00:00:00', $start), date('Y-m-d 23:59:59', $end)];
}

function datetime_range_previous_week()
{
    $previous_week = strtotime("-1 week +1 day");

    $start_week = strtotime("last sunday midnight", $previous_week);
    $end_week = strtotime("next saturday", $start_week);

    return [date('Y-m-d 00:00:00', $start_week), date('Y-m-d 23:59:59', $end_week)];
}

function datetime_range_this_month()
{
    return [date('Y-m-01 00:00:00'), date('Y-m-t 23:59:59')];
}

function datetime_range_previous_month()
{
    $end = strtotime("last day of previous month");
    return [date('Y-m-01 00:00:00', $end), date('Y-m-d 23:59:59', $end)];
}

function years($from, $to)
{
    $years = [];
    for ($y = $from; $y <= $to; $y++) {
        $years[] = $y;
    }
    return $years;
}

function months()
{
    $months = [];

    for ($m = 1; $m <= 12; $m++) {
        $months[$m] = month_names($m);
    }

    return $months;
}

function current_user_id()
{
    return Auth::user()->id;
}

function empty_string_to_null(&$arr, $key)
{
    if (is_array($key)) {
        foreach ($key as $k) {
            empty_string_to_null($arr, $k);
        }
    }

    if (is_string($key) && empty($arr[$key])) {
        $arr[$key] = null;
    }
}

function current_date()
{
    return date('Y-m-d');
}

function current_time()
{
    return date('H:i:s');
}

function current_datetime()
{
    return date('Y-m-d H:i:s');
}

function time_from_datetime($datetime)
{
    $a = explode(' ', $datetime);
    return $a[1];
}

function date_from_datetime($datetime)
{
    $a = explode(' ', $datetime);
    return $a[0];
}

function fill_with_default_value(&$array, $keys, $default)
{
    foreach ($keys as $key) {
        if (empty($array[$key])) {
            $array[$key] = $default;
        }
    }
}

function number_from_input($input)
{
    return floatval(str_replace(',', '.', str_replace('.', '', $input)));
}

function datetime_from_input($str)
{
    $input = explode(' ', $str);
    $date = explode('-', $input[0]);

    $out =  "$date[2]-$date[1]-$date[0]";
    if (count($input) == 2) {
        $out .=  " $input[1]";
    }

    return $out;
}

function extract_daterange($daterange)
{
    if (preg_match("/^([0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])) - ([0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1]))$/", $daterange, $matches)) {
        return [$matches[1], $matches[4]];
    }
    return false;
}

function extract_daterange_from_input($daterange)
{
    if (preg_match("/^((0[1-9]|[1-2][0-9]|3[0-1])-(0[1-9]|1[0-2])-[0-9]{4}) - ((0[1-9]|[1-2][0-9]|3[0-1])-(0[1-9]|1[0-2])-[0-9]{4})$/", $daterange, $matches)) {
        return [$matches[1], $matches[4]];
    }
    return false;
}

function format_number($number, int $prec = 0)
{
    return number_format(floatval($number), $prec, ',', '.');
}

function str_to_double($str)
{
    return doubleVal(str_replace('.', '', $str));
}

function str_to_int($str)
{
    return intVal(str_replace('.', '', $str));
}

function format_datetime($date, $format = 'd/m/Y H:i:s')
{
    if (!$date) {
        return '?';
    }

    if (!$date instanceof Carbon) {
        $date = new Carbon($date);
    }

    return $date->translatedFormat($format);
}

function format_date($date, $format = 'd/m/Y')
{
    if (!$date instanceof Carbon) {
        $date = new Carbon($date);
    }
    return $date->translatedFormat($format);
}

function month_names($month)
{
    switch ((int)$month) {
        case 1:
            return "Januari";
        case 2:
            return "Februari";
        case 3:
            return "Maret";
        case 4:
            return "April";
        case 5:
            return "Mei";
        case 6:
            return "Juni";
        case 7:
            return "Juli";
        case 8:
            return "Agustus";
        case 9:
            return "September";
        case 10:
            return "Oktober";
        case 11:
            return "November";
        case 12:
            return "Desember";
    }
}

/**
 * Menghitung tanggal mulai dan tanggal akhir berdasarkan nama periode.
 *
 * @param string $period Nama periode (e.g., 'today', 'this_week', 'prev_month').
 * @return array Berisi 'start_date' dan 'end_date' dalam objek Carbon.
 */
function getDateRangeByPeriod(string $period): array
{
    $now = Carbon::now();
    $start_date = null;
    $end_date = null;

    switch ($period) {
        // --- Harian ---
        case 'today':
            $start_date = $now->copy()->startOfDay();
            $end_date = $now->copy()->endOfDay();
            break;

        case 'yesterday':
            $start_date = $now->copy()->subDay()->startOfDay();
            $end_date = $now->copy()->subDay()->endOfDay();
            break;

        // --- Mingguan ---
        case 'this_week':
            // Minggu dimulai pada Senin (default Carbon)
            $start_date = $now->copy()->startOfWeek();
            $end_date = $now->copy()->endOfWeek();
            break;

        case 'prev_week':
            $start_date = $now->copy()->subWeek()->startOfWeek();
            $end_date = $now->copy()->subWeek()->endOfWeek();
            break;

        // --- Bulanan ---
        case 'this_month':
            $start_date = $now->copy()->startOfMonth();
            $end_date = $now->copy()->endOfMonth();
            break;

        case 'prev_month':
            $start_date = $now->copy()->subMonth()->startOfMonth();
            $end_date = $start_date->copy()->endOfMonth();
            break;

        case 'prev_2month':
            $start_date = $now->copy()->subMonth(2)->startOfMonth();
            $end_date = $start_date->copy()->endOfMonth();
            break;

        case 'prev_3month':
            $start_date = $now->copy()->subMonth(3)->startOfMonth();
            $end_date = $start_date->copy()->endOfMonth();
            break;

        // --- Tahunan ---
        case 'this_year':
            $start_date = $now->copy()->startOfYear();
            $end_date = $now->copy()->endOfYear();
            break;

        case 'prev_year':
            $start_date = $now->copy()->subYear()->startOfYear();
            $end_date = $now->copy()->subYear()->endOfYear();
            break;

        // --- Default ---
        default:
            // Default ke 'this_month' jika periode tidak valid
            $start_date = $now->copy()->startOfMonth();
            $end_date = $now->copy()->endOfMonth();
            break;
    }

    return [
        'start_date' => $start_date,
        'end_date' => $end_date,
    ];
}
