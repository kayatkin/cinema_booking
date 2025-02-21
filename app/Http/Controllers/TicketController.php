<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * Отображаем информацию о билете.
     */
    public function show(Ticket $ticket)
    {
        return view('tickets.show', compact('ticket'));
    }
}
