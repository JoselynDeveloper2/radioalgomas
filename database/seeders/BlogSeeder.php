<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuarios
        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'admin@tucanaltv.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
            'bio' => 'Administrador principal del sitio web de TuCanalTV.',
            'website' => 'https://tucanaltv.com',
        ]);

        $editor = User::create([
            'name' => 'Editor Principal',
            'email' => 'editor@tucanaltv.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'role' => User::ROLE_EDITOR,
            'is_active' => true,
            'bio' => 'Editor principal encargado de revisar y publicar contenido.',
        ]);

        $author = User::create([
            'name' => 'Periodista TuCanalTV',
            'email' => 'periodista@tucanaltv.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'role' => User::ROLE_AUTHOR,
            'is_active' => true,
            'bio' => 'Periodista especializado en noticias locales y nacionales.',
            'twitter' => '@periodista_tcv',
        ]);

        // Crear categorías
        $categories = [
            [
                'name' => 'Noticias Locales',
                'slug' => 'noticias-locales',
                'description' => 'Noticias y eventos de la comunidad local',
                'color' => '#3B82F6',
                'icon' => 'map-pin',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Deportes',
                'slug' => 'deportes',
                'description' => 'Cobertura deportiva local y nacional',
                'color' => '#10B981',
                'icon' => 'trophy',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Entretenimiento',
                'slug' => 'entretenimiento',
                'description' => 'Espectáculos, cultura y entretenimiento',
                'color' => '#F59E0B',
                'icon' => 'star',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Política',
                'slug' => 'politica',
                'description' => 'Noticias políticas y gubernamentales',
                'color' => '#EF4444',
                'icon' => 'building-government',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Economía',
                'slug' => 'economia',
                'description' => 'Noticias económicas y financieras',
                'color' => '#8B5CF6',
                'icon' => 'chart-line',
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($categories as $categoryData) {
            Category::create($categoryData);
        }

        // Crear etiquetas
        $tags = [
            ['name' => 'Urgente', 'slug' => 'urgente', 'color' => '#DC2626'],
            ['name' => 'Exclusiva', 'slug' => 'exclusiva', 'color' => '#7C3AED'],
            ['name' => 'Investigación', 'slug' => 'investigacion', 'color' => '#059669'],
            ['name' => 'Entrevista', 'slug' => 'entrevista', 'color' => '#DB2777'],
            ['name' => 'Opinión', 'slug' => 'opinion', 'color' => '#EA580C'],
            ['name' => 'Análisis', 'slug' => 'analisis', 'color' => '#0891B2'],
            ['name' => 'Reportaje', 'slug' => 'reportaje', 'color' => '#65A30D'],
            ['name' => 'En Vivo', 'slug' => 'en-vivo', 'color' => '#DC2626'],
        ];

        foreach ($tags as $tagData) {
            Tag::create($tagData);
        }

        // Obtener categorías y etiquetas creadas
        $localCategory = Category::where('slug', 'noticias-locales')->first();
        $sportsCategory = Category::where('slug', 'deportes')->first();
        $entertainmentCategory = Category::where('slug', 'entretenimiento')->first();
        $politicsCategory = Category::where('slug', 'politica')->first();
        $economyCategory = Category::where('slug', 'economia')->first();

        $urgentTag = Tag::where('slug', 'urgente')->first();
        $exclusiveTag = Tag::where('slug', 'exclusiva')->first();
        $interviewTag = Tag::where('slug', 'entrevista')->first();
        $analysisTag = Tag::where('slug', 'analisis')->first();

        // Crear artículos de ejemplo
        $articles = [
            [
                'title' => 'Nueva infraestructura vial mejorará conectividad en la ciudad',
                'excerpt' => 'El proyecto incluye la construcción de tres nuevos puentes y la ampliación de vías principales para reducir el tráfico vehicular.',
                'content' => '<p>La alcaldía anunció oficialmente el inicio de un ambicioso proyecto de infraestructura vial que transformará la movilidad urbana en los próximos dos años.</p><p>El plan contempla la construcción de tres puentes estratégicos que conectarán las zonas norte y sur de la ciudad, además de la ampliación de cuatro avenidas principales que actualmente presentan congestión vehicular.</p><p>"Este proyecto representa una inversión histórica en infraestructura que beneficiará directamente a más de 200,000 habitantes", declaró el alcalde durante la presentación oficial.</p><p>Las obras comenzarán el próximo mes y se estima que estarán completadas en 24 meses, con un presupuesto total de 50 millones de dólares financiados con recursos municipales y aportes del gobierno nacional.</p>',
                'status' => 'published',
                'published_at' => now()->subDays(1),
                'category_id' => $localCategory->id,
                'user_id' => $author->id,
                'is_featured' => true,
                'views_count' => 1250,
            ],
            [
                'title' => 'Equipo local clasifica a semifinales del torneo nacional',
                'excerpt' => 'Los Leones de la ciudad derrotaron 3-1 a sus rivales y se acercan al título nacional después de 15 años.',
                'content' => '<p>En un partido emocionante que mantuvo a los aficionados al borde de sus asientos, Los Leones lograron una victoria contundente que los coloca a un paso de la final nacional.</p><p>El encuentro, disputado en el estadio municipal ante más de 15,000 espectadores, mostró el mejor nivel del equipo local en toda la temporada.</p><p>Los goles fueron anotados por Rodríguez en el minuto 23, Martínez en el 67 y el gol que selló la victoria llegó en el tiempo de descuento por parte del capitán Fernández.</p><p>"Estamos viviendo un momento histórico. El equipo ha demostrado que tiene la calidad para competir al más alto nivel", comentó el entrenador tras el partido.</p><p>La semifinal se jugará el próximo sábado en la capital, donde Los Leones enfrentarán al actual campeón defensor.</p>',
                'status' => 'published',
                'published_at' => now()->subHours(6),
                'category_id' => $sportsCategory->id,
                'user_id' => $author->id,
                'is_featured' => false,
                'views_count' => 890,
            ],
            [
                'title' => 'Festival de música local reunirá a 20 artistas este fin de semana',
                'excerpt' => 'El evento gratuito se realizará en el parque central y contará con la participación de bandas emergentes y consagradas.',
                'content' => '<p>La tercera edición del Festival de Música Local promete ser la más grande hasta la fecha, con una programación que incluye diversos géneros musicales y actividades para toda la familia.</p><p>El evento, que se realizará durante todo el fin de semana en el parque central, contará con dos escenarios principales y un área especial para artistas emergentes.</p><p>Entre los artistas confirmados se encuentran la banda de rock "Ecos Urbanos", el grupo de folk "Raíces del Valle" y el dúo de música electrónica "Pulso Digital".</p><p>"Queremos que este festival se convierta en una tradición que celebre el talento local y fortalezca nuestra identidad cultural", explicó la directora de cultura municipal.</p><p>La entrada será completamente gratuita y se espera la asistencia de más de 5,000 personas durante los dos días del evento.</p>',
                'status' => 'published',
                'published_at' => now()->subHours(12),
                'category_id' => $entertainmentCategory->id,
                'user_id' => $editor->id,
                'is_featured' => true,
                'views_count' => 2100,
            ],
            [
                'title' => 'Consejo municipal aprueba presupuesto para el próximo año',
                'excerpt' => 'La sesión extraordinaria definió la asignación de recursos con énfasis en educación, salud e infraestructura.',
                'content' => '<p>En una sesión que se extendió por más de seis horas, el consejo municipal aprobó por mayoría el presupuesto que regirá las finanzas públicas durante el próximo año fiscal.</p><p>El presupuesto total asciende a 120 millones de dólares, representando un incremento del 15% respecto al año anterior, con prioridades claramente definidas en sectores estratégicos.</p><p>La educación recibirá el 35% de los recursos, seguida por salud con un 25% e infraestructura con un 20%. El restante 20% se distribuirá entre seguridad, cultura y programas sociales.</p><p>"Este presupuesto refleja nuestro compromiso con el desarrollo sostenible y el bienestar de todos los ciudadanos", declaró la presidenta del consejo.</p><p>La oposición expresó algunas reservas sobre la asignación para seguridad, considerándola insuficiente dado el crecimiento urbano.</p>',
                'status' => 'published',
                'published_at' => now()->subDays(2),
                'category_id' => $politicsCategory->id,
                'user_id' => $editor->id,
                'is_featured' => false,
                'views_count' => 675,
            ],
            [
                'title' => 'Nuevas empresas tecnológicas generan 500 empleos en la región',
                'excerpt' => 'El parque tecnológico atrae inversión extranjera y se consolida como hub de innovación en el país.',
                'content' => '<p>El sector tecnológico local experimenta un crecimiento sin precedentes con la llegada de cinco nuevas empresas que han establecido sus operaciones en el parque tecnológico municipal.</p><p>Estas compañías, especializadas en desarrollo de software, inteligencia artificial y servicios digitales, han creado 500 nuevos puestos de trabajo directos en los últimos seis meses.</p><p>"Estamos posicionando a nuestra ciudad como un centro de innovación tecnológica que atrae talento y genera oportunidades de empleo de alta calidad", señaló el director del parque tecnológico.</p><p>Las empresas han invertido un total de 25 millones de dólares en infraestructura y equipamiento, con planes de expansión que podrían duplicar la plantilla laboral en el próximo año.</p><p>El gobierno local ha implementado incentivos fiscales y programas de capacitación para apoyar este crecimiento del sector tecnológico.</p>',
                'status' => 'published',
                'published_at' => now()->subDays(3),
                'category_id' => $economyCategory->id,
                'user_id' => $author->id,
                'is_featured' => false,
                'views_count' => 1420,
            ],
        ];

        foreach ($articles as $articleData) {
            $article = Article::create($articleData);
            
            // Asignar etiquetas aleatoriamente
            $randomTags = collect([$urgentTag, $exclusiveTag, $interviewTag, $analysisTag])
                ->random(rand(1, 2));
            
            $article->tags()->attach($randomTags->pluck('id'));
        }

        $this->command->info('Blog seeder ejecutado exitosamente!');
        $this->command->info('Usuarios creados: 3');
        $this->command->info('Categorías creadas: 5');
        $this->command->info('Etiquetas creadas: 8');
        $this->command->info('Artículos creados: 5');
    }
}
