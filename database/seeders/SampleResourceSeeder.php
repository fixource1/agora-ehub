<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Category;
use App\Models\Resource;
use App\Models\ResourceFile;
use App\Models\ResourceMetadata;
use App\Models\ResourceType;
use App\Models\Tag;
use App\Models\User;
use Database\Seeders\Support\SampleCoverArt;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SampleResourceSeeder extends Seeder
{
    private const PUBLISHER = 'UPLB OVCRE Press';

    public function run(): void
    {
        $uploader = User::query()->where('email', 'author@agora-ehub.local')->first()
            ?? User::query()->first();

        if (! $uploader) {
            return;
        }

        $samples = [
            [
                'title' => 'DOST GIA Guidelines and Procedures',
                'subtitle' => 'Grants-In-Aid program compliance reference',
                'author' => 'Domingo Abucay',
                'type' => 'pdf-document',
                'category' => 'Guidelines',
                'description' => 'Official reference for DOST Grants-In-Aid (GIA) proposal preparation, fund utilization, reporting, and liquidation requirements for UPLB researchers and extension personnel.',
                'audience_level' => 'faculty',
                'metadata' => [
                    'publication_date' => '2024-03-15',
                    'page_count' => 96,
                ],
                'file' => ['name' => 'DOST GIA Guidelines and Procedures.pdf', 'type' => 'pdf', 'size' => 5400000],
                'tags' => ['dost', 'gia', 'grants', 'guidelines'],
            ],
            [
                'title' => 'REPS Guidelines Handbook',
                'subtitle' => 'Research, Extension, and Professional Staff',
                'author' => 'Rosalie Orma',
                'type' => 'pdf-document',
                'category' => 'Guidelines',
                'description' => 'Comprehensive handbook on REPS appointment, promotion, performance evaluation, and research productivity expectations at UPLB.',
                'audience_level' => 'faculty',
                'metadata' => [
                    'publication_date' => '2023-11-20',
                    'page_count' => 128,
                ],
                'file' => ['name' => 'REPS Guidelines Handbook.pdf', 'type' => 'pdf', 'size' => 7200000],
                'tags' => ['reps', 'guidelines', 'ovcre', 'hr'],
            ],
            [
                'title' => 'DOST GIA Monitoring and Evaluation Manual',
                'author' => 'Czarlina May Magnata',
                'type' => 'pdf-document',
                'category' => 'Guidelines',
                'description' => 'Step-by-step guide for terminal and progress reporting, financial accountability, and project monitoring of DOST-funded GIA projects.',
                'metadata' => [
                    'publication_date' => '2024-01-10',
                    'page_count' => 74,
                ],
                'file' => ['name' => 'DOST GIA Monitoring and Evaluation Manual.pdf', 'type' => 'pdf', 'size' => 4100000],
                'tags' => ['dost', 'gia', 'monitoring', 'evaluation'],
            ],
            [
                'title' => 'UPLB Research Ethics Review Protocol',
                'author' => 'Domingo Abucay',
                'type' => 'pdf-document',
                'category' => 'Guidelines',
                'description' => 'Institutional protocol for ethical clearance, informed consent, and responsible conduct of research involving human and animal subjects.',
                'metadata' => [
                    'publication_date' => '2022-08-05',
                    'page_count' => 52,
                ],
                'file' => ['name' => 'UPLB Research Ethics Review Protocol.pdf', 'type' => 'pdf', 'size' => 2800000],
                'tags' => ['ethics', 'research', 'protocol', 'guidelines'],
            ],
            [
                'title' => 'Research Proposal Writing for OVCRE',
                'author' => 'Rosalie Orma',
                'type' => 'pdf-document',
                'category' => 'Training Materials',
                'description' => 'Training module on structuring competitive research proposals, aligning with UPLB and DOST review criteria, and preparing supporting documents.',
                'metadata' => [
                    'publication_date' => '2024-06-01',
                    'page_count' => 64,
                ],
                'file' => ['name' => 'Research Proposal Writing for OVCRE.pdf', 'type' => 'pdf', 'size' => 3600000],
                'tags' => ['proposal', 'training', 'research', 'ovcre'],
            ],
            [
                'title' => 'Extension Project Implementation Guide',
                'author' => 'Czarlina May Magnata',
                'type' => 'pdf-document',
                'category' => 'Guidelines',
                'description' => 'Guidelines for planning, implementing, and documenting extension projects including stakeholder engagement and impact assessment.',
                'metadata' => [
                    'publication_date' => '2023-05-18',
                    'page_count' => 88,
                ],
                'file' => ['name' => 'Extension Project Implementation Guide.pdf', 'type' => 'pdf', 'size' => 4900000],
                'tags' => ['extension', 'guidelines', 'community'],
            ],
            [
                'title' => 'REPS Performance Evaluation Toolkit',
                'author' => 'Rosalie Orma',
                'type' => 'pdf-document',
                'category' => 'Research',
                'description' => 'Forms, rubrics, and evidence templates for documenting REPS research outputs, extension deliverables, and professional development.',
                'metadata' => [
                    'publication_date' => '2024-02-28',
                    'page_count' => 44,
                ],
                'file' => ['name' => 'REPS Performance Evaluation Toolkit.pdf', 'type' => 'pdf', 'size' => 2200000],
                'tags' => ['reps', 'evaluation', 'toolkit'],
            ],
            [
                'title' => 'OVCRE Annual Research Forum Proceedings',
                'subtitle' => 'Selected papers and abstracts',
                'author' => 'Domingo Abucay',
                'type' => 'ebook',
                'category' => 'Research',
                'description' => 'Compiled proceedings from the UPLB OVCRE Annual Research Forum featuring faculty research highlights across colleges and units.',
                'metadata' => [
                    'publication_date' => '2023-12-12',
                    'page_count' => 210,
                ],
                'file' => ['name' => 'OVCRE Annual Research Forum Proceedings.epub', 'type' => 'epub', 'size' => 9800000],
                'tags' => ['proceedings', 'research', 'forum'],
            ],
            [
                'title' => 'Technology Transfer and Commercialization Primer',
                'author' => 'Czarlina May Magnata',
                'type' => 'video',
                'category' => 'Training Materials',
                'description' => 'Video orientation on intellectual property disclosure, technology transfer pathways, and OVCRE support for research commercialization.',
                'metadata' => [
                    'publication_date' => '2024-04-22',
                    'duration_seconds' => 1680,
                ],
                'file' => ['name' => 'Technology Transfer and Commercialization Primer.mp4', 'type' => 'mp4', 'size' => 98500000],
                'tags' => ['technology-transfer', 'ip', 'video'],
            ],
            [
                'title' => 'Introduction to UPLB OVCRE Services',
                'author' => 'Domingo Abucay',
                'type' => 'audio',
                'category' => 'Education',
                'description' => 'Audio overview of OVCRE units, research administration services, funding opportunities, and support available to UPLB faculty and REPS.',
                'metadata' => [
                    'publication_date' => '2024-07-01',
                    'duration_seconds' => 1320,
                ],
                'file' => ['name' => 'Introduction to UPLB OVCRE Services.mp3', 'type' => 'mp3', 'size' => 24500000],
                'tags' => ['ovcre', 'orientation', 'audio'],
            ],
        ];

        $newSlugs = collect($samples)->map(fn (array $sample) => Str::slug($sample['title']))->all();

        Resource::query()
            ->whereNotIn('slug', $newSlugs)
            ->whereHas('files', fn ($query) => $query->where('file_path', 'like', 'resources/samples/%'))
            ->each(fn (Resource $resource) => $resource->delete());

        foreach ($samples as $sample) {
            $type = ResourceType::query()->where('slug', $sample['type'])->first();
            $category = Category::query()->where('slug', Str::slug($sample['category']))->first();

            if (! $type) {
                continue;
            }

            $slug = Str::slug($sample['title']);

            $coverImage = SampleCoverArt::ensure(
                $slug,
                $sample['title'],
                $sample['category'],
                $type->slug,
                $sample['file']['type'],
            );

            $resource = Resource::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'uploader_id' => $uploader->id,
                    'resource_type_id' => $type->id,
                    'category_id' => $category?->id,
                    'title' => $sample['title'],
                    'subtitle' => $sample['subtitle'] ?? null,
                    'description' => $sample['description'],
                    'cover_image' => $coverImage,
                    'language' => $sample['language'] ?? 'en',
                    'audience_level' => $sample['audience_level'] ?? 'general',
                    'status' => 'published',
                    'view_count' => random_int(40, 500),
                    'download_count' => random_int(10, 120),
                    'published_at' => now()->subDays(random_int(1, 90)),
                ],
            );

            $author = Author::query()->firstOrCreate(
                ['slug' => Str::slug($sample['author'])],
                ['name' => $sample['author']],
            );

            $resource->authors()->sync([
                $author->id => ['role' => 'author', 'sort_order' => 0],
            ]);

            ResourceMetadata::query()->updateOrCreate(
                ['resource_id' => $resource->id],
                array_merge(
                    ['publisher' => self::PUBLISHER],
                    $sample['metadata'],
                ),
            );

            ResourceFile::query()->updateOrCreate(
                [
                    'resource_id' => $resource->id,
                    'file_name' => $sample['file']['name'],
                ],
                [
                    'file_path' => 'resources/samples/'.$sample['file']['name'],
                    'disk' => 'local',
                    'mime_type' => $this->mimeFor($sample['file']['type']),
                    'file_type' => $sample['file']['type'],
                    'file_size' => $sample['file']['size'],
                    'version' => '1.0',
                    'is_primary' => true,
                    'is_downloadable' => true,
                ],
            );

            $tagIds = collect($sample['tags'])->map(function (string $name) {
                return Tag::query()->firstOrCreate(
                    ['slug' => Str::slug($name)],
                    ['name' => ucfirst(str_replace('-', ' ', $name))],
                )->id;
            });

            $resource->tags()->sync($tagIds);
        }
    }

    private function mimeFor(string $type): string
    {
        return match ($type) {
            'epub' => 'application/epub+zip',
            'pdf' => 'application/pdf',
            'mp4' => 'video/mp4',
            'mp3' => 'audio/mpeg',
            'txt' => 'text/plain',
            default => 'application/octet-stream',
        };
    }
}
