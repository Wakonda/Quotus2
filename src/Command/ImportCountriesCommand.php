<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Country;
use App\Entity\Language;

class ImportCountriesCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:import-countries';

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
		parent::__construct();
        $this->em = $em;
    }
	
    protected function configure()
    {
        // ...
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
		$datas = "Afghanistan;af;afFlag.png;afghanistan;1
				Afrique du Sud;za;zaFlag.png;afrique-du-sud;1
				Îles Åland;ax;axFlag.png;iles-aland;1
				Albanie;al;alFlag.png;albanie;1
				Algérie;dz;dzFlag.png;algerie;1
				Allemagne;de;deFlag.png;allemagne;1
				Andorre;ad;adFlag.png;andorre;1
				Angola;ao;aoFlag.png;angola;1
				Anguilla;ai;aiFlag.png;anguilla;1
				Antarctique;aq;aqFlag.png;antarctique;1
				Antigua-et-Barbuda;ag;agFlag.png;antigua-et-barbuda;1
				Arabie saoudite;sa;saFlag.png;arabie-saoudite;1
				Argentine;ar;arFlag.png;argentine;1
				Arménie;am;amFlag.png;armenie;1
				Aruba;aw;awFlag.png;aruba;1
				Australie;au;auFlag.png;australie;1
				Autriche;at;atFlag.png;autriche;1
				Azerbaïdjan;az;azFlag.png;azerbaidjan;1
				Bahamas;bs;bsFlag.png;bahamas;1
				Bahreïn;bh;bhFlag.png;bahrein;1
				Bangladesh;bd;bdFlag.png;bangladesh;1
				Barbade;bb;bbFlag.png;barbade;1
				Biélorussie;by;byFlag.png;bielorussie;1
				Belgique;be;beFlag.png;belgique;1
				Belize;bz;bzFlag.png;belize;1
				Bénin;bj;bjFlag.png;benin;1
				Bermudes;bm;bmFlag.png;bermudes;1
				Bhoutan;bt;btFlag.png;bhoutan;1
				Bolivie;bo;boFlag.png;bolivie;1
				Pays-Bas caribéens;bq;bqFlag.png;pays-bas-caribeens;1
				Bosnie-Herzégovine;ba;baFlag.png;bosnie-herzegovine;1
				Botswana;bw;bwFlag.png;botswana;1
				Île Bouvet;bv;bvFlag.png;ile-bouvet;1
				Brésil;br;brFlag.png;bresil;1
				Brunei;bn;bnFlag.png;brunei;1
				Bulgarie;bg;bgFlag.png;bulgarie;1
				Burkina Faso;bf;bfFlag.png;burkina-faso;1
				Burundi;bi;biFlag.png;burundi;1
				Îles Caïmans;ky;kyFlag.png;iles-caimans;1
				Cambodge;kh;khFlag.png;cambodge;1
				Cameroun;cm;cmFlag.png;cameroun;1
				Canada;ca;caFlag.png;canada;1
				Cap-Vert;cv;cvFlag.png;cap-vert;1
				République centrafricaine;cf;cfFlag.png;republique-centrafricaine;1
				Chili;cl;clFlag.png;chili;1
				Chine;cn;cnFlag.png;chine;1
				Île Christmas;cx;cxFlag.png;ile-christmas;1
				Chypre (pays);cy;cyFlag.png;chypre-pays;1
				Îles Cocos;cc;ccFlag.png;iles-cocos;1
				Colombie;co;coFlag.png;colombie;1
				Comores (pays);km;kmFlag.png;comores-pays;1
				République du Congo;cg;cgFlag.png;republique-du-congo;1
				République démocratique du Congo;cd;cdFlag.png;republique-democratique-du-congo;1
				Îles Cook;ck;ckFlag.png;iles-cook;1
				Corée du Sud;kr;krFlag.png;coree-du-sud;1
				Corée du Nord;kp;kpFlag.png;coree-du-nord;1
				Costa Rica;cr;crFlag.png;costa-rica;1
				Côte d'Ivoire;ci;ciFlag.png;cote-d-ivoire;1
				Croatie;hr;hrFlag.png;croatie;1
				Cuba;cu;cuFlag.png;cuba;1
				Curaçao;cw;cwFlag.png;curacao;1
				Danemark;dk;dkFlag.png;danemark;1
				Djibouti;dj;djFlag.png;djibouti;1
				République dominicaine;do;doFlag.png;republique-dominicaine;1
				Dominique;dm;dmFlag.png;dominique;1
				Égypte;eg;egFlag.png;egypte;1
				Salvador;sv;svFlag.png;salvador;1
				Émirats arabes unis;ae;aeFlag.png;emirats-arabes-unis;1
				Équateur (pays);ec;ecFlag.png;equateur-pays;1
				Érythrée;er;erFlag.png;erythree;1
				Espagne;es;esFlag.png;espagne;1
				Estonie;ee;eeFlag.png;estonie;1
				États-Unis;us;usFlag.png;etats-unis;1
				Éthiopie;et;etFlag.png;ethiopie;1
				Malouines;fk;fkFlag.png;malouines;1
				Îles Féroé;fo;foFlag.png;iles-feroe;1
				Fidji;fj;fjFlag.png;fidji;1
				Finlande;fi;fiFlag.png;finlande;1
				France;fr;frFlag.png;france;1
				Gabon;ga;gaFlag.png;gabon;1
				Gambie;gm;gmFlag.png;gambie;1
				Géorgie (pays);ge;geFlag.png;georgie-pays;1
				Géorgie du Sud-et-les Îles Sandwich du Sud;gs;gsFlag.png;georgie-du-sud-et-les-iles-sandwich-du-sud;1
				Ghana;gh;ghFlag.png;ghana;1
				Gibraltar;gi;giFlag.png;gibraltar;1
				Grèce;gr;grFlag.png;grece;1
				Grenade (pays);gd;gdFlag.png;grenade-pays;1
				Groenland;gl;glFlag.png;groenland;1
				Guadeloupe;gp;gpFlag.png;guadeloupe;1
				Guam;gu;guFlag.png;guam;1
				Guatemala;gt;gtFlag.png;guatemala;1
				Guernesey;gg;ggFlag.png;guernesey;1
				Guinée;gn;gnFlag.png;guinee;1
				Guinée-Bissau;gw;gwFlag.png;guinee-bissau;1
				Guinée équatoriale;gq;gqFlag.png;guinee-equatoriale;1
				Guyana;gy;gyFlag.png;guyana;1
				Guyane;gf;gfFlag.png;guyane;1
				Haïti;ht;htFlag.png;haiti;1
				Îles Heard-et-MacDonald;hm;hmFlag.png;iles-heard-et-macdonald;1
				Honduras;hn;hnFlag.png;honduras;1
				Hong Kong;hk;hkFlag.png;hong-kong;1
				Hongrie;hu;huFlag.png;hongrie;1
				Île de Man;im;imFlag.png;ile-de-man;1
				Îles mineures éloignées des États-Unis;um;umFlag.png;iles-mineures-eloignees-des-etats-unis;1
				Îles Vierges britanniques;vg;vgFlag.png;iles-vierges-britanniques;1
				Îles Vierges des États-Unis;vi;viFlag.png;iles-vierges-des-etats-unis;1
				Inde;in;inFlag.png;inde;1
				Indonésie;id;idFlag.png;indonesie;1
				Iran;ir;irFlag.png;iran;1
				Irak;iq;iqFlag.png;irak;1
				Irlande (pays);ie;ieFlag.png;irlande-pays;1
				Islande;is;isFlag.png;islande;1
				Israël;il;ilFlag.png;israel;1
				Italie;it;itFlag.png;italie;1
				Jamaïque;jm;jmFlag.png;jamaique;1
				Japon;jp;jpFlag.png;japon;1
				Jersey;je;jeFlag.png;jersey;1
				Jordanie;jo;joFlag.png;jordanie;1
				Kazakhstan;kz;kzFlag.png;kazakhstan;1
				Kenya;ke;keFlag.png;kenya;1
				Kirghizistan;kg;kgFlag.png;kirghizistan;1
				Kiribati;ki;kiFlag.png;kiribati;1
				Koweït;kw;kwFlag.png;koweit;1
				Laos;la;laFlag.png;laos;1
				Lesotho;ls;lsFlag.png;lesotho;1
				Lettonie;lv;lvFlag.png;lettonie;1
				Liban;lb;lbFlag.png;liban;1
				Liberia;lr;lrFlag.png;liberia;1
				Libye;ly;lyFlag.png;libye;1
				Liechtenstein;li;liFlag.png;liechtenstein;1
				Lituanie;lt;ltFlag.png;lituanie;1
				Luxembourg (pays);lu;luFlag.png;luxembourg-pays;1
				Macao;mo;moFlag.png;macao;1
				Macédoine (pays);mk;mkFlag.png;macedoine-pays;1
				Madagascar;mg;mgFlag.png;madagascar;1
				Malaisie;my;myFlag.png;malaisie;1
				Malawi;mw;mwFlag.png;malawi;1
				Maldives;mv;mvFlag.png;maldives;1
				Mali;ml;mlFlag.png;mali;1
				Malte;mt;mtFlag.png;malte;1
				Îles Mariannes du Nord;mp;mpFlag.png;iles-mariannes-du-nord;1
				Maroc;ma;maFlag.png;maroc;1
				Marshall (pays);mh;mhFlag.png;marshall-pays;1
				Martinique;mq;mqFlag.png;martinique;1
				Maurice (pays);mu;muFlag.png;maurice-pays;1
				Mauritanie;mr;mrFlag.png;mauritanie;1
				Mayotte;yt;ytFlag.png;mayotte;1
				Mexique;mx;mxFlag.png;mexique;1
				Micronésie (pays);fm;fmFlag.png;micronesie-pays;1
				Moldavie;md;mdFlag.png;moldavie;1
				Monaco;mc;mcFlag.png;monaco;1
				Mongolie;mn;mnFlag.png;mongolie;1
				Monténégro;me;meFlag.png;montenegro;1
				Montserrat;ms;msFlag.png;montserrat;1
				Mozambique;mz;mzFlag.png;mozambique;1
				Birmanie;mm;mmFlag.png;birmanie;1
				Namibie;na;naFlag.png;namibie;1
				Nauru;nr;nrFlag.png;nauru;1
				Népal;np;npFlag.png;nepal;1
				Nicaragua;ni;niFlag.png;nicaragua;1
				Niger;ne;neFlag.png;niger;1
				Nigeria;ng;ngFlag.png;nigeria;1
				Nioué;nu;nuFlag.png;nioue;1
				Île Norfolk;nf;nfFlag.png;ile-norfolk;1
				Norvège;no;noFlag.png;norvege;1
				Nouvelle-Calédonie;nc;ncFlag.png;nouvelle-caledonie;1
				Nouvelle-Zélande;nz;nzFlag.png;nouvelle-zelande;1
				Territoire britannique de l'océan Indien;io;ioFlag.png;territoire-britannique-de-l-ocean-indien;1
				Oman;om;omFlag.png;oman;1
				Ouganda;ug;ugFlag.png;ouganda;1
				Ouzbékistan;uz;uzFlag.png;ouzbekistan;1
				Pakistan;pk;pkFlag.png;pakistan;1
				Palaos;pw;pwFlag.png;palaos;1
				Palestine;ps;psFlag.png;palestine;1
				Panama;pa;paFlag.png;panama;1
				Papouasie-Nouvelle-Guinée;pg;pgFlag.png;papouasie-nouvelle-guinee;1
				Paraguay;py;pyFlag.png;paraguay;1
				Pays-Bas;nl;nlFlag.png;pays-bas;1
				Pérou;pe;peFlag.png;perou;1
				Philippines;ph;phFlag.png;philippines;1
				Îles Pitcairn;pn;pnFlag.png;iles-pitcairn;1
				Pologne;pl;plFlag.png;pologne;1
				Polynésie française;pf;pfFlag.png;polynesie-francaise;1
				Porto Rico;pr;prFlag.png;porto-rico;1
				Portugal;pt;ptFlag.png;portugal;1
				Qatar;qa;qaFlag.png;qatar;1
				La Réunion;re;reFlag.png;la-reunion;1
				Roumanie;ro;roFlag.png;roumanie;1
				Royaume-Uni;gb;gbFlag.png;royaume-uni;1
				Russie;ru;ruFlag.png;russie;1
				Rwanda;rw;rwFlag.png;rwanda;1
				Sahara occidental;eh;ehFlag.png;sahara-occidental;1
				Saint-Barthélemy;bl;blFlag.png;saint-barthelemy;1
				Saint-Christophe-et-Niévès;kn;knFlag.png;saint-christophe-et-nieves;1
				Saint-Marin;sm;smFlag.png;saint-marin;1
				Saint-Martin;mf;mfFlag.png;saint-martin;1
				Sint Maarten;sx;sxFlag.png;sint-maarten;1
				Saint-Pierre-et-Miquelon;pm;pmFlag.png;saint-pierre-et-miquelon;1
				Vatican (État de la Cité du Vatican);va;vaFlag.png;vatican-etat-de-la-cite-du-vatican;1
				Saint-Vincent-et-les Grenadines;vc;vcFlag.png;saint-vincent-et-les-grenadines;1
				Sainte-Hélène|;sh;shFlag.png;sainte-helene;1
				Sainte-Lucie;lc;lcFlag.png;sainte-lucie;1
				Salomon;sb;sbFlag.png;salomon;1
				Samoa;ws;wsFlag.png;samoa;1
				Samoa américaines;as;asFlag.png;samoa-americaines;1
				Sao Tomé-et-Principe;st;stFlag.png;sao-tome-et-principe;1
				Sénégal;sn;snFlag.png;senegal;1
				Serbie;rs;rsFlag.png;serbie;1
				Seychelles;sc;scFlag.png;seychelles;1
				Sierra Leone;sl;slFlag.png;sierra-leone;1
				Singapour;sg;sgFlag.png;singapour;1
				Slovaquie;sk;skFlag.png;slovaquie;1
				Slovénie;si;siFlag.png;slovenie;1
				Somalie;so;soFlag.png;somalie;1
				Soudan;sd;sdFlag.png;soudan;1
				Soudan du Sud;ss;ssFlag.png;soudan-du-sud;1
				Sri Lanka;lk;lkFlag.png;sri-lanka;1
				Suède;se;seFlag.png;suede;1
				Suisse;ch;chFlag.png;suisse;1
				Suriname;sr;srFlag.png;suriname;1
				Svalbard et ile Jan Mayen;sj;sjFlag.png;svalbard-et-ile-jan-mayen;1
				Swaziland;sz;szFlag.png;swaziland;1
				Syrie;sy;syFlag.png;syrie;1
				Tadjikistan;tj;tjFlag.png;tadjikistan;1
				Taïwan / (République de Chine (Taïwan));tw;twFlag.png;taiwan-republique-de-chine-taiwan;1
				Tanzanie;tz;tzFlag.png;tanzanie;1
				Tchad;td;tdFlag.png;tchad;1
				République tchèque;cz;czFlag.png;republique-tcheque;1
				Terres australes et antarctiques françaises;tf;tfFlag.png;terres-australes-et-antarctiques-francaises;1
				Thaïlande;th;thFlag.png;thailande;1
				Timor oriental;tl;tlFlag.png;timor-oriental;1
				Togo;tg;tgFlag.png;togo;1
				Tokelau;tk;tkFlag.png;tokelau;1
				Tonga;to;toFlag.png;tonga;1
				Trinité-et-Tobago;tt;ttFlag.png;trinite-et-tobago;1
				Tunisie;tn;tnFlag.png;tunisie;1
				Turkménistan;tm;tmFlag.png;turkmenistan;1
				Îles Turques-et-Caïques;tc;tcFlag.png;iles-turques-et-caiques;1
				Turquie;tr;trFlag.png;turquie;1
				Tuvalu;tv;tvFlag.png;tuvalu;1
				Ukraine;ua;uaFlag.png;ukraine;1
				Uruguay;uy;uyFlag.png;uruguay;1
				Vanuatu;vu;vuFlag.png;vanuatu;1
				Venezuela;ve;veFlag.png;venezuela;1
				Viêt Nam;vn;vnFlag.png;viet-nam;1
				Wallis-et-Futuna;wf;wfFlag.png;wallis-et-futuna;1
				Yémen;ye;yeFlag.png;yemen;1
				Zambie;zm;zmFlag.png;zambie;1
				Zimbabwe;zw;zwFlag.png;zimbabwe;1";
		
		$datasArray = explode("\r\n", $datas);
		
		foreach($datasArray as $res)
		{
			$resArray = explode(";", trim($res));
		
			$language = $this->em->getRepository(Language::class)->findOneBy(["abbreviation" => "fr"]);
			$country = $this->em->getRepository(Country::class)->findOneBy(["internationalName" => trim($resArray[1]), "language" => $language]);
			
			if(empty($country))
				$country = new Country();
		
			$country->setTitle($resArray[0]);
			$country->setInternationalName($resArray[1]);
			$country->setFlag($resArray[2]);
			$country->setSlug($resArray[3]);
			$country->setLanguage($language);
		
			$this->em->persist($country);
		}
		
		$this->em->flush();
    }
}