<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MeetingTemplateController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\AnalyticsController;

// Auth routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/', [MeetingController::class, 'dashboard'])->name('dashboard');

    // Manager & Admin routes (meeting management) - HARUS DI ATAS
    Route::middleware('manager')->group(function () {
        Route::get('meetings/create', [MeetingController::class, 'create'])->name('meetings.create');
        Route::post('meetings', [MeetingController::class, 'store'])->name('meetings.store');
        Route::get('meetings/{meeting}/edit', [MeetingController::class, 'edit'])->name('meetings.edit');
        Route::put('meetings/{meeting}', [MeetingController::class, 'update'])->name('meetings.update');
        Route::delete('meetings/{meeting}', [MeetingController::class, 'destroy'])->name('meetings.destroy');
        Route::post('meetings/{meeting}/status', [MeetingController::class, 'updateStatus'])->name('meetings.updateStatus');
        Route::get('meetings-bulk', [MeetingController::class, 'bulk'])->name('meetings.bulk');
        Route::post('meetings/bulk-update-status', [MeetingController::class, 'bulkUpdateStatus'])->name('meetings.bulkUpdateStatus');
        Route::post('meetings/bulk-delete', [MeetingController::class, 'bulkDelete'])->name('meetings.bulkDelete');
        Route::get('meetings/export', [MeetingController::class, 'exportMeetings'])->name('meetings.export');
        Route::resource('templates', MeetingTemplateController::class);
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.exportPdf');
        Route::get('reports/export-excel', [ReportController::class, 'exportExcel'])->name('reports.exportExcel');
        Route::get('statistics', [StatisticsController::class, 'index'])->name('statistics.index');
        Route::get('statistics/api', [StatisticsController::class, 'api'])->name('statistics.api');
        Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
        Route::get('analytics/api', [AnalyticsController::class, 'api'])->name('analytics.api');
        Route::get('analytics/export-pdf', [AnalyticsController::class, 'exportPdf'])->name('analytics.exportPdf');
        Route::get('analytics/export-excel', [AnalyticsController::class, 'exportExcel'])->name('analytics.exportExcel');
    });
    
    // Meeting routes (all users)
    Route::get('meetings', [MeetingController::class, 'index'])->name('meetings.index');
    Route::get('meetings/{meeting}', [MeetingController::class, 'show'])->name('meetings.show');
    Route::get('calendar', [MeetingController::class, 'calendar'])->name('calendar');
    Route::get('calendar-data', [MeetingController::class, 'getCalendarData'])->name('calendar.data');
    Route::get('meetings-graph', [MeetingController::class, 'graph'])->name('meetings.graph');
    Route::get('meetings-graph-data', [MeetingController::class, 'getGraphData'])->name('meetings.graphData');
    Route::post('meetings/check-conflict', [MeetingController::class, 'checkConflict'])->name('meetings.checkConflict');
    
    // Admin only routes (system management)
    Route::middleware('admin')->group(function () {
        Route::resource('rooms', RoomController::class)->except(['create', 'edit']);
        Route::resource('participants', ParticipantController::class)->except(['create', 'edit']);
    });
});
