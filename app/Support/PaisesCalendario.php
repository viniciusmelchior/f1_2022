<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * MELHORIA 20261119: países do mundo para o calendário (sede automática e bandeiras).
 * Cada item: "código ISO usado pelo flagcdn:nome|outro nome|...". O primeiro nome é o oficial em português.
 * Nomes com "*" (cidades, ex.: Dubai, New York) apontam para o país, mas a sede mostrada é o nome oficial.
 * A mesma lista é usada no JavaScript da página (calendario/index.blade.php), para as regras serem iguais.
 */
class PaisesCalendario
{
    public const LISTA = [
        'af:Afeganistão|Afghanistan',
        'za:África do Sul|South Africa',
        'al:Albânia|Albania',
        'de:Alemanha|Germany',
        'ad:Andorra',
        'ao:Angola',
        'ag:Antígua e Barbuda|Antigua and Barbuda',
        'sa:Arábia Saudita|Saudi Arabia',
        'dz:Argélia|Algeria',
        'ar:Argentina',
        'am:Armênia|Armenia',
        'au:Austrália|Australia',
        'at:Áustria|Austria',
        'az:Azerbaijão|Azerbaijan',
        'bs:Bahamas',
        'bh:Bahrein|Bahrain|Barein|Bareine',
        'bd:Bangladesh',
        'bb:Barbados',
        'by:Bielorrússia|Belarus|Bielorrusia',
        'be:Bélgica|Belgium',
        'bz:Belize',
        'bj:Benin',
        'bo:Bolívia|Bolivia',
        'ba:Bósnia e Herzegovina|Bósnia|Bosnia and Herzegovina|Bosnia',
        'bw:Botsuana|Botswana',
        'br:Brasil|Brazil',
        'bn:Brunei',
        'bg:Bulgária|Bulgaria',
        'bf:Burkina Faso',
        'bi:Burundi',
        'bt:Butão|Bhutan',
        'cv:Cabo Verde|Cape Verde',
        'cm:Camarões|Cameroon',
        'kh:Camboja|Cambodia',
        'ca:Canadá|Canada',
        'qa:Catar|Qatar',
        'kz:Cazaquistão|Kazakhstan',
        'td:Chade|Chad',
        'cl:Chile',
        'cn:China',
        'cy:Chipre|Cyprus',
        'co:Colômbia|Colombia',
        'km:Comores|Comoros',
        'cg:Congo|República do Congo|Congo-Brazzaville',
        'cd:República Democrática do Congo|RD Congo|RDC|Congo-Kinshasa|DR Congo',
        'kp:Coreia do Norte|North Korea',
        'kr:Coreia do Sul|Coreia|Coréia|Coréia do Sul|South Korea|Korea',
        'ci:Costa do Marfim|Ivory Coast|Côte d\'Ivoire',
        'cr:Costa Rica',
        'hr:Croácia|Croatia',
        'cu:Cuba',
        'dk:Dinamarca|Denmark',
        'dj:Djibuti|Djibouti',
        'dm:Dominica',
        'eg:Egito|Egypt',
        'sv:El Salvador',
        'ae:Emirados Árabes Unidos|Emirados Árabes|Emirados|United Arab Emirates|UAE|*Abu Dhabi|*Dubai',
        'ec:Equador|Ecuador',
        'er:Eritreia|Eritrea',
        'sk:Eslováquia|Slovakia',
        'si:Eslovênia|Slovenia',
        'es:Espanha|Spain',
        'us:Estados Unidos|Estados Unidos da América|EUA|USA|United States|US|*New Jersey|*Nova Jersey|*New York|*Nova York|*Nova Iorque',
        'ee:Estônia|Estonia',
        'sz:Essuatíni|Eswatini|Suazilândia|Swaziland',
        'et:Etiópia|Ethiopia',
        'fj:Fiji',
        'ph:Filipinas|Philippines',
        'fi:Finlândia|Finland',
        'fr:França|France',
        'ga:Gabão|Gabon',
        'gm:Gâmbia|Gambia',
        'gh:Gana|Ghana',
        'ge:Geórgia|Georgia',
        'gd:Granada|Grenada',
        'gr:Grécia|Greece',
        'gt:Guatemala',
        'gy:Guiana|Guyana',
        'gn:Guiné|Guinea',
        'gq:Guiné Equatorial|Equatorial Guinea',
        'gw:Guiné-Bissau|Guinea-Bissau',
        'ht:Haiti',
        'nl:Holanda|Países Baixos|Netherlands|Holland',
        'hn:Honduras',
        'hu:Hungria|Hungary',
        'ye:Iêmen|Iemen|Yemen',
        'mh:Ilhas Marshall|Marshall Islands',
        'sb:Ilhas Salomão|Solomon Islands',
        'in:Índia|India',
        'id:Indonésia|Indonesia',
        'ir:Irã|Irão|Iran',
        'iq:Iraque|Iraq',
        'ie:Irlanda|Ireland',
        'is:Islândia|Iceland',
        'il:Israel',
        'it:Itália|Italy',
        'jm:Jamaica',
        'jp:Japão|Japan',
        'jo:Jordânia|Jordan',
        'ki:Kiribati',
        'xk:Kosovo',
        'kw:Kuwait|Kuweit',
        'la:Laos',
        'ls:Lesoto|Lesotho',
        'lv:Letônia|Latvia',
        'lb:Líbano|Lebanon',
        'lr:Libéria|Liberia',
        'ly:Líbia|Libya',
        'li:Liechtenstein',
        'lt:Lituânia|Lithuania',
        'lu:Luxemburgo|Luxembourg',
        'mk:Macedônia do Norte|Macedônia|North Macedonia|Macedonia',
        'mg:Madagascar',
        'my:Malásia|Malaysia',
        'mw:Malawi|Malaui',
        'mv:Maldivas|Maldives',
        'ml:Mali',
        'mt:Malta',
        'ma:Marrocos|Morocco',
        'mu:Maurício|Ilhas Maurício|Mauritius',
        'mr:Mauritânia|Mauritania',
        'mx:México|Mexico',
        'mm:Mianmar|Myanmar|Birmânia|Burma',
        'fm:Micronésia|Micronesia',
        'mz:Moçambique|Mozambique',
        'md:Moldávia|Moldova',
        'mc:Mônaco|Monaco',
        'mn:Mongólia|Mongolia',
        'me:Montenegro',
        'na:Namíbia|Namibia',
        'nr:Nauru',
        'np:Nepal',
        'ni:Nicarágua|Nicaragua',
        'ne:Níger|Niger',
        'ng:Nigéria|Nigeria',
        'no:Noruega|Norway',
        'nz:Nova Zelândia|New Zealand',
        'om:Omã|Oman',
        'pw:Palau',
        'ps:Palestina|Palestine',
        'pa:Panamá|Panama',
        'pg:Papua-Nova Guiné|Papua New Guinea',
        'pk:Paquistão|Pakistan',
        'py:Paraguai|Paraguay',
        'pe:Peru',
        'pl:Polônia|Poland',
        'pt:Portugal',
        'ke:Quênia|Kenya',
        'kg:Quirguistão|Kyrgyzstan',
        'gb:Reino Unido|Grã-Bretanha|United Kingdom|UK|Great Britain|Britain',
        'gb-eng:Inglaterra|England',
        'gb-sct:Escócia|Scotland',
        'gb-wls:País de Gales|Gales|Wales',
        'gb-nir:Irlanda do Norte|Northern Ireland',
        'cf:República Centro-Africana|Central African Republic',
        'cz:República Tcheca|Tchéquia|Chéquia|Czech Republic|Czechia',
        'do:República Dominicana|Dominican Republic',
        'ro:Romênia|Romania',
        'rw:Ruanda|Rwanda',
        'ru:Rússia|Russia',
        'ws:Samoa',
        'sm:San Marino',
        'lc:Santa Lúcia|Saint Lucia',
        'kn:São Cristóvão e Névis|Saint Kitts and Nevis',
        'st:São Tomé e Príncipe|Sao Tome and Principe',
        'vc:São Vicente e Granadinas|Saint Vincent and the Grenadines',
        'sc:Seicheles|Seychelles',
        'sn:Senegal',
        'sl:Serra Leoa|Sierra Leone',
        'rs:Sérvia|Serbia',
        'sg:Singapura|Singapore',
        'sy:Síria|Syria',
        'so:Somália|Somalia',
        'lk:Sri Lanka',
        'sd:Sudão|Sudan',
        'ss:Sudão do Sul|South Sudan',
        'se:Suécia|Sweden',
        'ch:Suíça|Switzerland',
        'sr:Suriname',
        'th:Tailândia|Thailândia|Thailandia|Thailand',
        'tw:Taiwan',
        'tj:Tajiquistão|Tadjiquistão|Tajikistan',
        'tz:Tanzânia|Tanzania',
        'tl:Timor-Leste|Timor Leste|East Timor',
        'tg:Togo',
        'to:Tonga',
        'tt:Trinidad e Tobago|Trinidad and Tobago',
        'tn:Tunísia|Tunisia',
        'tm:Turcomenistão|Turquemenistão|Turkmenistan',
        'tr:Turquia|Türkiye|Turkey',
        'tv:Tuvalu',
        'ua:Ucrânia|Ukraine',
        'ug:Uganda',
        'uy:Uruguai|Uruguay',
        'uz:Uzbequistão|Uzbekistan',
        'vu:Vanuatu',
        'va:Vaticano|Santa Sé|Vatican',
        've:Venezuela',
        'vn:Vietnã|Vietname|Vietnam',
        'zm:Zâmbia|Zambia',
        'zw:Zimbábue|Zimbabwe',
        'hk:Hong Kong',
        'mo:Macau|Macao',
        'pr:Porto Rico|Puerto Rico',
        'gl:Groenlândia|Greenland',
        'fo:Ilhas Faroé|Ilhas Feroé|Faroe Islands',
        'gi:Gibraltar',
        'im:Ilha de Man|Isle of Man',
        'je:Jersey',
        'gg:Guernsey',
        'bm:Bermudas|Bermuda',
        'ky:Ilhas Cayman|Cayman Islands',
        'vg:Ilhas Virgens Britânicas|British Virgin Islands',
        'vi:Ilhas Virgens Americanas|US Virgin Islands',
        'aw:Aruba',
        'cw:Curaçao',
        'sx:Sint Maarten',
        'gp:Guadalupe|Guadeloupe',
        'mq:Martinica|Martinique',
        'gf:Guiana Francesa|French Guiana',
        're:Reunião|Réunion|Reunion',
        'yt:Mayotte',
        'nc:Nova Caledônia|New Caledonia',
        'pf:Polinésia Francesa|Taiti|Tahiti|French Polynesia',
        'gu:Guam',
        'as:Samoa Americana|American Samoa',
        'mp:Ilhas Marianas do Norte|Northern Mariana Islands',
        'ck:Ilhas Cook|Cook Islands',
        'eh:Saara Ocidental|Western Sahara',
        'ax:Ilhas Åland|Åland',
        'ai:Anguila|Anguilla',
        'ms:Montserrat',
        'tc:Ilhas Turcas e Caicos|Turks and Caicos',
        'fk:Ilhas Malvinas|Malvinas|Falkland Islands|Falklands',
        'sh:Santa Helena|Saint Helena',
        'pm:São Pedro e Miquelão|Saint Pierre and Miquelon',
        'wf:Wallis e Futuna|Wallis and Futuna',
        'nu:Niue',
        'nf:Ilha Norfolk|Norfolk Island',
        'cx:Ilha Christmas|Christmas Island',
        'cc:Ilhas Cocos|Cocos Islands',
        'tk:Tokelau',
        'pn:Ilhas Pitcairn|Pitcairn Islands',
        'io:Território Britânico do Oceano Índico|British Indian Ocean Territory',
        'bl:São Bartolomeu|Saint Barthélemy',
        'mf:São Martinho|Saint Martin',
        'bq:Caribe Neerlandês|Bonaire|Caribbean Netherlands',
        'sj:Svalbard e Jan Mayen|Svalbard',
        'aq:Antártida|Antártica|Antarctica',
        'bv:Ilha Bouvet|Bouvet Island',
        'gs:Ilhas Geórgia do Sul e Sandwich do Sul|Geórgia do Sul|South Georgia',
        'hm:Ilhas Heard e McDonald|Heard Island',
        'tf:Terras Austrais Francesas|French Southern Territories',
        'um:Ilhas Menores Distantes dos Estados Unidos|United States Minor Outlying Islands'
    ];

    private static ?array $mapa = null;
    private static ?array $ordenados = null;

    public static function normalizar(?string $texto): string
    {
        $t = mb_strtolower(Str::ascii((string) $texto));
        $t = preg_replace("/[-'\x{2019}]/u", ' ', $t);

        return trim(preg_replace('/\s+/', ' ', $t));
    }

    /** nome normalizado => [código, nome para exibir] */
    private static function mapa(): array
    {
        if (self::$mapa === null) {
            self::$mapa = [];
            foreach (self::LISTA as $linha) {
                [$codigo, $nomes] = explode(':', $linha, 2);
                $nomes = explode('|', $nomes);
                $oficial = ltrim($nomes[0], '*');
                foreach ($nomes as $nome) {
                    $cidade = str_starts_with($nome, '*');
                    $nome = ltrim($nome, '*');
                    self::$mapa[self::normalizar($nome)] ??= [$codigo, $cidade ? $oficial : $nome]; // o primeiro escrito vale (ex.: Coreia, não Coréia)
                }
            }
            self::$ordenados = array_keys(self::$mapa);
            usort(self::$ordenados, fn ($a, $b) => strlen($b) <=> strlen($a)); // mais longo primeiro
        }

        return self::$mapa;
    }

    /** Acha o país citado no texto (inteiro, partes separadas por "/" ou dentro do texto). */
    public static function encontrar(?string $texto): ?array
    {
        $mapa = self::mapa();
        $k = self::normalizar($texto);
        if ($k === '') {
            return null;
        }
        if (isset($mapa[$k])) {
            return $mapa[$k];
        }
        foreach (explode('/', $k) as $parte) {
            $parte = trim($parte);
            if (isset($mapa[$parte])) {
                return $mapa[$parte];
            }
        }
        $frase = ' ' . trim(preg_replace('/\s+/', ' ', preg_replace('/[^a-z0-9 ]/', ' ', $k))) . ' ';
        foreach (self::$ordenados as $nome) {
            if (strlen($nome) >= 4 && str_contains($frase, ' ' . $nome . ' ')) {
                return $mapa[$nome];
            }
        }

        return null;
    }

    /**
     * Sede automática a partir do nome da etapa/sprint: só o país ("GP da Itália" -> Itália, "Sprint Brasil" -> Brasil).
     * Sem país conhecido no nome: tira "GP", "Grande Prêmio", "Sprint", "Corrida" e "da/do/de..." do começo.
     */
    public static function sede(?string $nome): string
    {
        $nome = trim((string) $nome);
        if ($nome === '') {
            return '';
        }
        if ($achou = self::encontrar($nome)) {
            return $achou[1];
        }
        $resto = preg_replace('/^\s*(g\.?\s?p\.?|grande\s+pr[eê]mio|sprint|corrida)(?=[\s\-:]|$)/iu', '', $nome);
        $resto = preg_replace('/^[\s\-\x{2013}:\x{00B7}]+/u', '', $resto);
        $resto = preg_replace('/^(da|do|de|dos|das|di|del|of|the)\s+/iu', '', $resto);

        return trim($resto);
    }
}
