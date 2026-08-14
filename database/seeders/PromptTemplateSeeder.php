<?php

namespace Database\Seeders;

use App\Models\PromptTemplate;
use Illuminate\Database\Seeder;

class PromptTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'title'      => 'Code Review & Edge Cases',
                'shortcut'   => 'code-review',
                'category'   => 'Coding',
                'content'    => "Please conduct a thorough code review for the following code snippet. Identify any bugs, security vulnerabilities, edge cases, performance bottlenecks, and suggest clean refactoring improvements:\n\n```\n\n```",
                'sort_order' => 1,
            ],
            [
                'title'      => 'Refactor & Optimize Code',
                'shortcut'   => 'refactor',
                'category'   => 'Coding',
                'content'    => "Refactor the following code to adhere to clean code principles, DRY patterns, and modern performance best practices:\n\n",
                'sort_order' => 2,
            ],
            [
                'title'      => 'Generate Unit Tests',
                'shortcut'   => 'unit-test',
                'category'   => 'Coding',
                'content'    => "Write comprehensive unit tests covering success, failure, and edge cases for the following code:\n\n",
                'sort_order' => 3,
            ],
            [
                'title'      => 'Explain Like I\'m 5 (ELI5)',
                'shortcut'   => 'explain',
                'category'   => 'Learning',
                'content'    => "Explain the following technical concept in simple, easy-to-understand terms using analogies as if I am 5 years old:\n\n",
                'sort_order' => 4,
            ],
            [
                'title'      => 'Polish Grammar & Professional Tone',
                'shortcut'   => 'grammar',
                'category'   => 'Writing',
                'content'    => "Polish the grammar, flow, and tone of the following text to make it clear, persuasive, and professional:\n\n",
                'sort_order' => 5,
            ],
            [
                'title'      => 'Executive Summary (Bullet Points)',
                'shortcut'   => 'summarize',
                'category'   => 'Analysis',
                'content'    => "Provide an executive summary of the following document/text in concise, high-impact bullet points:\n\n",
                'sort_order' => 6,
            ],
        ];

        foreach ($templates as $t) {
            PromptTemplate::updateOrCreate(['shortcut' => $t['shortcut']], $t);
        }
    }
}
