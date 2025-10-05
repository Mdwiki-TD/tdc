<div class='card'>
    <div class='card-header'>
        <span class="h4">
            <a target="_blank" href="https://www.wikidata.org/wiki/Q107014860#sitelinks-wikipedia">
                <img src="https://www.wikidata.org/static/favicon/wikidata.ico" width="25" alt="Wikidata favicon">
            </a>
            Translations Categories</span>
    </div>
    <div class='card-body'>
        <table class="table table-sm table-striped table_text_left" id="categories_table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Language</th>
                    <th>Already created</th>
                    <th>Not Created</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
<!-- langs_url example: { "results": [ { "lang": "ar" }, { "lang": "or" }, { "lang": "zh" }, ... } -->

<!-- rest_api example: { "orwiki": { "title": "ଶ୍ରେଣୀ:Translated from MDWiki", "badges": [], "url": "https://or.wikipedia.org/wiki/ଶ୍ରେଣୀ:Translated_from_MDWiki" }, "arwiki": { "title": "تصنيف:Translated from MDWiki", "badges": [], "url": "https://ar.wikipedia.org/wiki/تصنيف:Translated_from_MDWiki" }, ... } -->

<script>
    const langs_url = `/api.php?get=pages&select=lang&distinct=1&lang=not_empty`;
    const rest_api = `https://www.wikidata.org/w/rest.php/wikibase/v1/entities/items/Q107014860/sitelinks`;

    $('#categories_table').DataTable({
        stateSave: true,
        paging: false,
        info: false,
        searching: false,
        ajax: function(data, callback, settings) {
            // Fetch data from both sources with Promise.all
            Promise.all([
                fetch(langs_url).then(res => res.json()),
                fetch(rest_api).then(res => res.json())
            ]).then(([langsData, restData]) => {
                let langs = langsData.results.map(r => r.lang);
                let categoryCount = 0;
                let fallbackCount = 0;

                let results = langs.map((lang, i) => {
                    let key = lang + "wiki";
                    let row = {
                        lang: lang,
                        category: "",
                        fallback: ""
                    };

                    if (restData[key]) {
                        row.category = `<a target="_blank" href="${restData[key].url}">${restData[key].title}</a>`;
                        categoryCount++;
                    } else {
                        const fallbackUrl = `https://${lang}.wikipedia.org/wiki/Category:Translated_from_MDWiki`;
                        row.fallback = `⚠️ <a target="_blank" href="${fallbackUrl}">Category:Translated from MDWiki</a>`;
                        fallbackCount++;
                    }

                    return row;
                });

                // Column counters
                $('#cat_count').text(categoryCount);
                $('#fallback_count').text(fallbackCount);

                callback({
                    data: results
                });
            });
        },
        columns: [{
                data: null,
                title: '#',
                render: function(data, type, row, meta) {
                    return meta.row + 1;
                },
                className: 'dt-center'
            },
            {
                data: 'lang',
                title: 'Language'
            },
            {
                data: 'category',
                title: 'Already created (<span id="cat_count">0</span>)'
            },
            {
                data: 'fallback',
                title: 'Not Created (<span id="fallback_count">0</span>)'
            }
        ]
    });
</script>
