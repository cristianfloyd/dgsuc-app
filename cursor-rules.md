# Cursor AI Rules for Laravel Admin Development

## Core Development Context
- PHP 8.3+
- Laravel 11
- Multiple Database Configuration
  - Primary: Local application data
  - Secondary: HR system integration

## Technology Stack Guidelines
- FilamentPHP for admin panels
- Livewire for dynamic interfaces
- TALL stack integration
- Spatie package tools
- Laravel conventions

## Coding Standards
```php
declare(strict_types=1);

namespace App\Filament\Resources;

class HRResource extends Resource
{
    protected static ?string $model = HRModel::class;
    
    // Use snake_case for properties
    protected static string $navigation_group = 'HR Management';
    
    // Use camelCase for methods
    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }
}
```

## Database Integration Rules
- Models for HR system should use read-only connection
- Implement repository pattern for data access
- Cache frequently accessed HR data
- Use database transactions for data integrity

## Filament Resource Patterns
```php
public static function getWidgets(): array
{
    return [
        HRMetricsWidget::class,
        StaffingOverviewWidget::class,
        PerformanceReportsWidget::class,
    ];
}

public static function getPages(): array
{
    return [
        'index' => Pages\ListHRRecords::route('/'),
        'create' => Pages\CreateHRRecord::route('/create'),
        'reports' => Pages\GenerateHRReports::route('/reports'),
    ];
}
```

## Custom Business Logic Implementation
- Use dedicated Services for complex HR calculations
- Implement Events for HR data changes
- Create custom Actions for bulk operations
- Use Policies for access control

## Report Generation Rules
- Use Laravel Excel for exports
- Implement caching for report data
- Create reusable chart components
- Use scheduled commands for periodic reports

## Testing Requirements
```php
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HRResourceTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_can_view_hr_dashboard(): void
    {
        // Test implementation
    }
}
```
