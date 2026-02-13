<?php
namespace App\Models\Ewms;

use Illuminate\Database\Eloquent\Model;

class Summary extends Model
{
    protected $connection = 'ewms';
    protected $table = 'table_summary';
    protected $primaryKey = 'id';
    public $timestamps = false;
    
    // Add accessors to convert string to float
    protected $appends = ['atl_max_float', 'actual_atl_float'];
    
    protected $casts = [
        'actual_atl' => 'float',
        'actual_ac' => 'float',
        'actual_asrs_rd' => 'float',
        'actual_rep' => 'float',
        'actual_acef' => 'float',
    ];
    
    // Accessor for atl_max as float
    public function getAtlMaxFloatAttribute()
    {
        return $this->convertToFloat($this->atl_max);
    }
    
    // Accessor for actual_atl as float (in case it's also string)
    public function getActualAtlFloatAttribute()
    {
        return $this->convertToFloat($this->actual_atl);
    }
    
    // Helper method to convert string to float
    private function convertToFloat($value)
    {
        if (is_numeric($value)) {
            return (float) $value;
        }
        
        // Try to extract numbers from string
        if (is_string($value)) {
            preg_match('/[-+]?[0-9]*\.?[0-9]+/', $value, $matches);
            if (!empty($matches)) {
                return (float) $matches[0];
            }
        }
        
        return 0.0;
    }
    
    // Regular relationships
    public function faculty()
    {
        return $this->belongsTo(FacultyProfile::class, 'f_id', 'id');
    }
    
    public function semester()
    {
        return $this->belongsTo(Semester::class, 'sem_id', 'sem_id');
    }
}