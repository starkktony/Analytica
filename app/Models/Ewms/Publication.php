<?php
// app/Models/Ewms/Publication.php

namespace App\Models\Ewms;

use Illuminate\Database\Eloquent\Model;

class Publication extends Model
{
    protected $connection = 'ewms';
    protected $table = 'table_publication';
    protected $primaryKey = 'id';
    public $timestamps = false;
    
    protected $casts = [
        'f_id' => 'integer',
        'no_of_pages' => 'integer',
    ];
    
    protected $fillable = [
        'id',
        'f_id',
        'referred_program',
        'publication_title',
        'abstract',
        'authors',
        'outside_authors',
        'editors',
        'publisher',
        'journal_title',
        'doi_link',
        'journal_index',
        'others',
        'type',
        'no_of_pages',
        'vol_iss_no',
        'issn_isbn',
        'date',
        'involve_center',
        'file_name',
        'attachment',
        'folderId',
    ];
    
    public function faculty()
    {
        return $this->belongsTo(FacultyProfile::class, 'f_id', 'id');
    }
    
    // Helper to get publication year
    public function getPublicationYearAttribute()
    {
        if ($this->date && is_numeric($this->date)) {
            return (int) $this->date;
        }
        
        // Try to extract year from date string
        if (is_string($this->date)) {
            preg_match('/\d{4}/', $this->date, $matches);
            if (!empty($matches)) {
                return (int) $matches[0];
            }
        }
        
        return null;
    }
}