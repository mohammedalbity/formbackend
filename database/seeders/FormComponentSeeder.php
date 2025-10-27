<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FormComponent;

class FormComponentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $components = [
            // Basic Components
            [
                'type' => 'textfield',
                'label' => 'Text Field',
                'label_ar' => 'حقل نص',
                'key' => 'textfield',
                'properties' => [
                    'placeholder' => 'Enter text',
                    'maxLength' => 255,
                    'inputType' => 'text'
                ],
                'validation' => [
                    'required' => false,
                    'minLength' => 0,
                    'maxLength' => 255
                ],
                'description' => 'A basic text input field',
                'description_ar' => 'حقل إدخال نص أساسي',
                'category' => 'basic',
                'sort_order' => 1,
                'icon' => 'text-field',
                'is_active' => true,
                'is_custom' => false,
            ],
            [
                'type' => 'textarea',
                'label' => 'Text Area',
                'label_ar' => 'منطقة نص',
                'key' => 'textarea',
                'properties' => [
                    'placeholder' => 'Enter text',
                    'rows' => 3,
                    'maxLength' => 1000
                ],
                'validation' => [
                    'required' => false,
                    'minLength' => 0,
                    'maxLength' => 1000
                ],
                'description' => 'A multi-line text input field',
                'description_ar' => 'حقل إدخال نص متعدد الأسطر',
                'category' => 'basic',
                'sort_order' => 2,
                'icon' => 'text-area',
                'is_active' => true,
                'is_custom' => false,
            ],
            [
                'type' => 'number',
                'label' => 'Number',
                'label_ar' => 'رقم',
                'key' => 'number',
                'properties' => [
                    'placeholder' => 'Enter number',
                    'step' => 1,
                    'min' => null,
                    'max' => null
                ],
                'validation' => [
                    'required' => false,
                    'min' => null,
                    'max' => null
                ],
                'description' => 'A numeric input field',
                'description_ar' => 'حقل إدخال رقمي',
                'category' => 'basic',
                'sort_order' => 3,
                'icon' => 'number',
                'is_active' => true,
                'is_custom' => false,
            ],
            [
                'type' => 'email',
                'label' => 'Email',
                'label_ar' => 'بريد إلكتروني',
                'key' => 'email',
                'properties' => [
                    'placeholder' => 'Enter email address',
                    'maxLength' => 255
                ],
                'validation' => [
                    'required' => false,
                    'pattern' => '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$'
                ],
                'description' => 'An email input field with validation',
                'description_ar' => 'حقل إدخال بريد إلكتروني مع التحقق',
                'category' => 'basic',
                'sort_order' => 4,
                'icon' => 'email',
                'is_active' => true,
                'is_custom' => false,
            ],
            [
                'type' => 'select',
                'label' => 'Select',
                'label_ar' => 'اختيار',
                'key' => 'select',
                'properties' => [
                    'placeholder' => 'Select an option',
                    'multiple' => false,
                    'options' => []
                ],
                'validation' => [
                    'required' => false
                ],
                'description' => 'A dropdown selection field',
                'description_ar' => 'حقل اختيار منسدل',
                'category' => 'basic',
                'sort_order' => 5,
                'icon' => 'select',
                'is_active' => true,
                'is_custom' => false,
            ],
            [
                'type' => 'checkbox',
                'label' => 'Checkbox',
                'label_ar' => 'مربع اختيار',
                'key' => 'checkbox',
                'properties' => [
                    'label' => 'Check this box',
                    'value' => true
                ],
                'validation' => [
                    'required' => false
                ],
                'description' => 'A checkbox input field',
                'description_ar' => 'حقل مربع اختيار',
                'category' => 'basic',
                'sort_order' => 6,
                'icon' => 'checkbox',
                'is_active' => true,
                'is_custom' => false,
            ],
            [
                'type' => 'radio',
                'label' => 'Radio',
                'label_ar' => 'اختيار واحد',
                'key' => 'radio',
                'properties' => [
                    'options' => [],
                    'inline' => false
                ],
                'validation' => [
                    'required' => false
                ],
                'description' => 'Radio button selection field',
                'description_ar' => 'حقل اختيار واحد',
                'category' => 'basic',
                'sort_order' => 7,
                'icon' => 'radio',
                'is_active' => true,
                'is_custom' => false,
            ],
            // Advanced Components
            [
                'type' => 'datetime',
                'label' => 'Date/Time',
                'label_ar' => 'تاريخ/وقت',
                'key' => 'datetime',
                'properties' => [
                    'format' => 'yyyy-MM-dd',
                    'enableTime' => false,
                    'enableDate' => true
                ],
                'validation' => [
                    'required' => false
                ],
                'description' => 'Date and time picker field',
                'description_ar' => 'حقل اختيار التاريخ والوقت',
                'category' => 'advanced',
                'sort_order' => 1,
                'icon' => 'calendar',
                'is_active' => true,
                'is_custom' => false,
            ],
            [
                'type' => 'file',
                'label' => 'File Upload',
                'label_ar' => 'رفع ملف',
                'key' => 'file',
                'properties' => [
                    'multiple' => false,
                    'maxSize' => '10MB',
                    'allowedTypes' => ['image/*', 'application/pdf']
                ],
                'validation' => [
                    'required' => false
                ],
                'description' => 'File upload field',
                'description_ar' => 'حقل رفع الملفات',
                'category' => 'advanced',
                'sort_order' => 2,
                'icon' => 'file-upload',
                'is_active' => true,
                'is_custom' => false,
            ],
            // Layout Components
            [
                'type' => 'htmlelement',
                'label' => 'HTML Element',
                'label_ar' => 'عنصر HTML',
                'key' => 'htmlelement',
                'properties' => [
                    'tag' => 'div',
                    'content' => '<p>HTML content here</p>',
                    'className' => ''
                ],
                'validation' => [],
                'description' => 'Custom HTML element',
                'description_ar' => 'عنصر HTML مخصص',
                'category' => 'layout',
                'sort_order' => 1,
                'icon' => 'code',
                'is_active' => true,
                'is_custom' => false,
            ],
        ];

        foreach ($components as $component) {
            FormComponent::create($component);
        }
    }
}
