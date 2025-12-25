<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->index(['start_time', 'end_time'], 'meetings_time_range_index');
            $table->index(['room_id', 'start_time'], 'meetings_room_time_index');
            $table->index(['status', 'start_time'], 'meetings_status_time_index');
            $table->index('created_at', 'meetings_created_at_index');
        });

        Schema::table('meeting_participants', function (Blueprint $table) {
            $table->index(['meeting_id', 'participant_id'], 'meeting_participants_composite_index');
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->index('name', 'rooms_name_index');
        });

        Schema::table('participants', function (Blueprint $table) {
            $table->index('email', 'participants_email_index');
            $table->index('name', 'participants_name_index');
        });

        Schema::table('meeting_templates', function (Blueprint $table) {
            $table->index(['is_active', 'created_by'], 'templates_active_creator_index');
        });
    }

    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropIndex('meetings_time_range_index');
            $table->dropIndex('meetings_room_time_index');
            $table->dropIndex('meetings_status_time_index');
            $table->dropIndex('meetings_created_at_index');
        });

        Schema::table('meeting_participants', function (Blueprint $table) {
            $table->dropIndex('meeting_participants_composite_index');
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropIndex('rooms_name_index');
        });

        Schema::table('participants', function (Blueprint $table) {
            $table->dropIndex('participants_email_index');
            $table->dropIndex('participants_name_index');
        });

        Schema::table('meeting_templates', function (Blueprint $table) {
            $table->dropIndex('templates_active_creator_index');
        });
    }
};