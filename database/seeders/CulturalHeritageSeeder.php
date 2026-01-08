<?php

namespace Database\Seeders;

use App\Models\CulturalHeritage;
use Illuminate\Database\Seeder;

class CulturalHeritageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contents = [
            [
                'title' => 'Yakan Weaving: A Living Heritage',
                'slug' => 'yakan-weaving-living-heritage',
                'category' => 'art',
                'summary' => 'Discover the ancient art of Yakan weaving that has been passed down through generations.',
                'content' => 'The art of Yakan weaving represents one of the most significant cultural treasures of the Yakan people. This intricate craft, characterized by its distinctive geometric patterns and vibrant colors, has been preserved and refined over centuries. Each piece tells a story of tradition, skill, and cultural identity.

The weaving process itself is a meditation on patience and precision. Using traditional looms and natural dyes, Yakan weavers create textiles that are not merely functional but are works of art that carry deep cultural significance. The patterns woven into each piece often represent historical events, spiritual beliefs, or family narratives.

Modern weavers continue to honor these traditions while adapting their techniques to contemporary demands. Many young Yakan artisans are learning from their elders, ensuring that this precious heritage continues to thrive in the modern world. Support for these crafts is crucial in maintaining the cultural and economic vitality of Yakan communities.',
                'author' => 'Yakan Heritage Foundation',
                'published_date' => now()->subDays(30),
                'image' => null,
                'is_published' => true,
                'order' => 1,
            ],
            [
                'title' => 'The Yakan Language: Voices of a People',
                'slug' => 'yakan-language-voices-people',
                'category' => 'culture',
                'summary' => 'Explore the unique linguistic heritage of the Yakan language and its cultural significance.',
                'content' => 'The Yakan language is an Austronesian language spoken by the Yakan people primarily in the Philippines. This distinct language carries within it centuries of cultural knowledge, historical memory, and worldview unique to the Yakan people.

The linguistic structure of Yakan reflects the people\'s deep connection to their environment, their social systems, and their spiritual beliefs. Many concepts and ideas that are fundamental to Yakan culture find their purest expression in the language itself, making it invaluable for understanding the community.

Like many indigenous languages, Yakan faces challenges in the modern era. However, community efforts to preserve and teach the language to younger generations are creating new hope. Educational programs and cultural initiatives are working to ensure that the Yakan language continues to be a living, vibrant means of communication and cultural expression.',
                'author' => 'Linguistic Studies Team',
                'published_date' => now()->subDays(25),
                'image' => null,
                'is_published' => true,
                'order' => 2,
            ],
            [
                'title' => 'Traditional Yakan Cuisine: Flavors of History',
                'slug' => 'traditional-yakan-cuisine',
                'category' => 'culture',
                'summary' => 'Discover the unique flavors and traditions behind Yakan culinary practices.',
                'content' => 'Yakan cuisine is a reflection of the people\'s geography, history, and cultural values. The flavors that characterize traditional Yakan food tell stories of maritime heritage, agricultural knowledge, and communal celebration.

Traditional dishes often feature local ingredients such as coconut, fish, and locally grown vegetables. The preparation methods, passed down through families and communities, embody principles of sustainability and resourcefulness. Many recipes are accompanied by rituals and customs that make dining a cultural experience rather than mere sustenance.

The spice combinations and cooking techniques used in Yakan cuisine are sophisticated and refined, developing over centuries of culinary evolution. Modern chefs and food enthusiasts are increasingly recognizing the value of traditional Yakan food, not only as a means of cultural preservation but also as part of global culinary dialogue.',
                'author' => 'Cultural Anthropology Institute',
                'published_date' => now()->subDays(20),
                'image' => null,
                'is_published' => true,
                'order' => 3,
            ],
            [
                'title' => 'Yakan Arts and Visual Traditions',
                'slug' => 'yakan-arts-visual-traditions',
                'category' => 'art',
                'summary' => 'Explore the visual arts and decorative traditions of the Yakan people.',
                'content' => 'The visual arts of the Yakan people encompass a wide range of artistic expressions, from intricate weaving patterns to decorative woodwork and metalcraft. These art forms are deeply integrated into daily life and spiritual practices, serving both aesthetic and ceremonial purposes.

Each artistic tradition has evolved with specific symbolic meanings. Colors, patterns, and designs are not random but carry significant cultural messages. For instance, certain patterns might commemorate important historical events or represent spiritual protection and blessings.

Contemporary Yakan artists are finding innovative ways to honor traditional forms while creating new artistic languages that speak to modern audiences. Art exhibitions and cultural festivals are increasingly showcasing these works to broader audiences, creating dialogue between traditional and contemporary artistic practices.',
                'author' => 'Yakan Arts Collective',
                'published_date' => now()->subDays(15),
                'image' => null,
                'is_published' => true,
                'order' => 4,
            ],
            [
                'title' => 'Yakan History: From Ancient Times to Today',
                'slug' => 'yakan-history-ancient-modern',
                'category' => 'history',
                'summary' => 'An overview of Yakan history, traditions, and their journey through time.',
                'content' => 'The history of the Yakan people is marked by resilience, adaptation, and cultural pride. From their earliest settlements in the Philippines to their present-day communities, the Yakan have maintained distinctive cultural practices while navigating significant historical changes.

Archaeological and oral historical evidence suggests that the Yakan people have inhabited their lands for centuries, developing sophisticated systems of agriculture, trade, and governance. Their maritime heritage connected them to broader regional trade networks, influencing cultural practices and economic structures.

Throughout various periods of colonial rule and modernization, the Yakan maintained their cultural identity through conscious preservation of traditions and adaptation strategies. This balance between preservation and innovation continues to define Yakan society today. Understanding this history is crucial for appreciating the contemporary Yakan community and supporting their continued cultural vitality.',
                'author' => 'Historical Research Bureau',
                'published_date' => now()->subDays(10),
                'image' => null,
                'is_published' => true,
                'order' => 5,
            ],
            [
                'title' => 'Yakan Festivals and Celebrations',
                'slug' => 'yakan-festivals-celebrations',
                'category' => 'tradition',
                'summary' => 'Learn about the vibrant festivals and celebrations that define Yakan cultural life.',
                'content' => 'Yakan festivals and celebrations are joyous expressions of cultural identity and communal values. These events bring communities together to honor their heritage, celebrate significant moments, and reinforce social bonds.

Traditional festivals often incorporate music, dance, food, and elaborate decorations that showcase the artistic talents of the community. Each celebration has specific meanings and purposes, from marking seasonal changes to commemorating historical events or spiritual occasions.

These celebrations are not mere entertainment but are educational experiences where younger generations learn about their culture. Through participation in these festivals, cultural knowledge is transmitted, traditions are reinforced, and community cohesion is strengthened. For visitors and outsiders, these festivals provide opportunities to experience and appreciate Yakan culture firsthand.',
                'author' => 'Community Events Center',
                'published_date' => now()->subDays(5),
                'image' => null,
                'is_published' => true,
                'order' => 6,
            ],
            [
                'title' => 'Yakan Traditional Beliefs and Spirituality',
                'slug' => 'yakan-traditional-beliefs',
                'category' => 'culture',
                'summary' => 'Understand the spiritual worldview of the Yakan people and their belief systems.',
                'content' => 'The traditional beliefs of the Yakan people form the spiritual foundation of their culture. These beliefs encompass respect for nature, veneration of ancestors, and adherence to ethical principles that guide communal life.

Yakan spirituality is characterized by a holistic worldview that sees interconnections between the physical and spiritual realms. This perspective influences daily practices, decision-making, and social relationships. Spiritual leaders and keepers of knowledge play important roles in maintaining these traditions and providing spiritual guidance to their communities.

While many Yakan people have embraced various world religions, traditional spiritual beliefs continue to influence cultural practices and worldview. This syncretic approach—blending traditional beliefs with contemporary religious practices—demonstrates the adaptability and resilience of Yakan culture. Understanding these spiritual traditions provides insight into Yakan values and their approach to life.',
                'author' => 'Spiritual Studies Department',
                'published_date' => now()->subDays(3),
                'image' => null,
                'is_published' => true,
                'order' => 7,
            ],
        ];

        foreach ($contents as $content) {
            CulturalHeritage::create($content);
        }
    }
}
