<?php

use App\Services\RssImportService;

test('media:thumbnail images are picked up at 800px (BBC Mundo style feeds)', function () {
    $feed = new SimplePie\SimplePie();
    $feed->set_raw_data(<<<'XML'
        <?xml version="1.0" encoding="UTF-8"?>
        <rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/">
          <channel><title>BBC</title>
            <item>
              <title>Nota</title>
              <link>https://www.bbc.com/mundo/articles/x</link>
              <description>Resumen</description>
              <media:thumbnail width="240" height="135" url="https://ichef.bbci.co.uk/ace/ws/240/foto.jpg"/>
            </item>
          </channel>
        </rss>
        XML);
    $feed->enable_cache(false);
    $feed->init();

    $extract = (new ReflectionMethod(RssImportService::class, 'extractEnclosures'))->getClosure(new RssImportService());

    expect(array_column($extract($feed->get_item(0)), 'url'))->toContain('https://ichef.bbci.co.uk/ace/ws/800/foto.jpg');
});
