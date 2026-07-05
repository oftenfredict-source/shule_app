<?php

namespace App\Services;

use App\Models\StoreMeasurementType;
use App\Models\StoreUnitOfMeasure;

class StoreDefaultsService
{
    public static function defaults(): array
    {
        return [
            'Quantity' => [
                'Piece (Pc)', 'Unit', 'Item', 'Pair', 'Set', 'Pack', 'Packet',
                'Bundle', 'Roll', 'Ream', 'Box', 'Carton', 'Case', 'Dozen', 'Score', 'Gross', 'Lot',
            ],
            'Weight' => [
                'Milligram (mg)', 'Gram (g)', 'Kilogram (kg)', 'Tonne (t)', 'Ounce (oz)', 'Pound (lb)',
            ],
            'Length' => [
                'Millimetre (mm)', 'Centimetre (cm)', 'Metre (m)', 'Kilometre (km)', 'Inch (in)', 'Foot (ft)', 'Yard (yd)',
            ],
            'Height' => [
                'Millimetre (mm)', 'Centimetre (cm)', 'Metre (m)', 'Foot (ft)', 'Inch (in)',
            ],
            'Width' => [
                'Millimetre (mm)', 'Centimetre (cm)', 'Metre (m)', 'Inch (in)', 'Foot (ft)',
            ],
            'Depth' => [
                'Millimetre (mm)', 'Centimetre (cm)', 'Metre (m)',
            ],
            'Thickness' => [
                'Millimetre (mm)', 'Centimetre (cm)', 'Micrometre (µm)',
            ],
            'Area' => [
                'Square Metre (m²)', 'Square Centimetre (cm²)', 'Square Foot (ft²)', 'Square Inch (in²)', 'Acre', 'Hectare',
            ],
            'Volume' => [
                'Millilitre (ml)', 'Litre (L)', 'Cubic Centimetre (cm³)', 'Cubic Metre (m³)', 'Gallon',
            ],
            'Capacity' => [
                'Millilitre (ml)', 'Litre (L)', 'Gallon', 'Barrel', 'Tank',
            ],
            'Count' => [
                'Unit', 'Piece (Pc)', 'Each', 'Head', 'Batch',
            ],
            'Percentage' => [
                'Percent (%)', 'Ratio', 'Fraction',
            ],
            'Time' => [
                'Second', 'Minute', 'Hour', 'Day', 'Week', 'Month', 'Year',
            ],
            'Temperature' => [
                'Celsius (°C)', 'Fahrenheit (°F)', 'Kelvin (K)',
            ],
            'Power' => [
                'Watt (W)', 'Kilowatt (kW)', 'Horsepower (HP)',
            ],
            'Energy' => [
                'Joule (J)', 'Kilojoule (kJ)', 'Kilowatt Hour (kWh)',
            ],
            'Pressure' => [
                'Pascal (Pa)', 'Bar', 'PSI',
            ],
            'Density' => [
                'Kilogram per Cubic Metre (kg/m³)', 'Gram per Cubic Centimetre (g/cm³)',
            ],
            'Speed' => [
                'Metre per Second (m/s)', 'Kilometre per Hour (km/h)', 'Mile per Hour (mph)',
            ],
            'Voltage' => [
                'Volt (V)', 'Millivolt (mV)', 'Kilovolt (kV)',
            ],
            'Current' => [
                'Ampere (A)', 'Milliampere (mA)',
            ],
            'Frequency' => [
                'Hertz (Hz)', 'Kilohertz (kHz)', 'Megahertz (MHz)',
            ],
            'Data Size' => [
                'Byte (B)', 'Kilobyte (KB)', 'Megabyte (MB)', 'Gigabyte (GB)', 'Terabyte (TB)',
            ],
            'Package' => [
                'Box', 'Carton', 'Pallet', 'Crate', 'Bag', 'Sack',
            ],
            'Custom' => [],
        ];
    }

    public static function seedForSchool(int $schoolID): void
    {
        $exists = StoreMeasurementType::where('schoolID', $schoolID)->exists();
        if ($exists) {
            return;
        }

        foreach (self::defaults() as $typeName => $units) {
            $type = StoreMeasurementType::create([
                'schoolID' => $schoolID,
                'type_name' => $typeName,
                'is_system' => true,
                'status' => 'Active',
            ]);

            foreach ($units as $unitName) {
                StoreUnitOfMeasure::create([
                    'measurement_typeID' => $type->measurement_typeID,
                    'unit_name' => $unitName,
                    'abbreviation' => null,
                    'is_system' => true,
                    'status' => 'Active',
                ]);
            }
        }
    }
}
