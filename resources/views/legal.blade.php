@extends('layouts.app-with-navbar')

@section('meta')
<meta name="robots" content="noindex, follow">
@endsection

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-gray-700 rounded-lg shadow px-8 mt-6 py-12">
        <div class="sm:mx-auto sm:w-full sm:max-w-sm">
            <h1 class="text-center text-2xl font-bold leading-9 tracking-tight">Mentions légales</h1>
        </div>
        <div class="mt-6">
            <div>
                <p>
                    Ce site web est fourni "tel quel" sans aucune garantie, expresse ou implicite.
                </p>
                <p>
                    Hébergeur : {{ config('legal.hosting_info') }}
                </p>
                <h2 class="text-xl font-normal leading-normal mt-2 mb-2">
                    Données personnelles
                </h2>
                <p>
                    Ce site web prend la protection des données au sérieux et ne partagera pas vos données personnelles avec des tiers.
                    Le refus de la collecte de données vous empêchera d'utiliser le service.<br />
                    Vous pouvez demander l'accès, la correction ou la suppression de vos données personnelles en envoyant un email à l'adresse suivante.<br />
                    DPO : {{ config('legal.dpo') }}<br />
                    Si vous estimez que ce site web viole vos droits, vous pouvez déposer une plainte auprès de la CNIL sur cnil.fr/plaintes.
                </p>
                <h3 class="text-xl font-normal leading-normal mt-2 mb-2">
                    Données de connexion et de navigation
                </h3>
                <p>
                    Ce site web collecte des données de connexion et de navigation des utilisateurs : date et heure de connexion, adresse IP, terminal, navigateur et système d'exploitation.<br />
                    Elles ne sont pas destinées à être stockées de manière permanente côté serveur ni à être utilisées à d'autres fins que de fournir l'accès à ce site web et à ses services.<br />
                    Parfois, elles peuvent être stockées dans des fichiers journaux sur le serveur lors de l'accès au site web, à des fins de débogage ou d'enregistrement (principalement en cas d'erreur, avec une trace d'erreur), et conservées pendant une durée maximale de {{ config('legal.logs_retention') }} mois.
                </p>
                <h3 class="text-xl font-normal leading-normal mt-0 mb-2">
                    Données des comptes
                </h3>
                <p>
                    Certaines informations personnelles (adresse e-mail et nom) sont collectées et utilisées pour créer un compte et accéder à des sections privées.<br />
                    Elles sont stockées côté serveur dans une base de données et utilisées à des fins de gestion des accès.<br />
                    Cependant, parfois, elles peuvent également être stockées dans des fichiers journaux sur le serveur lors de l'accès au site web, à des fins de débogage ou d'enregistrement (principalement en cas d'erreur, avec une trace d'erreur), et conservées pendant une durée maximale de {{ config('legal.logs_retention') }} mois.
                </p>
                <h3 class="text-xl font-normal leading-normal mt-2 mb-2">
                    PDFs de menus téléchargés
                </h3>
                <p>
                    Ce site web collecte des PDFs de menus.<br />
                    Ces fichiers sont stockés de manière permanente côté serveur et utilisés pour construire l'interface de ce site web.<br />
                    Assurez-vous que ces fichiers ne contiennent aucune donnée personnelle avant de les télécharger.
                </p>
                <h2 class="text-xl font-normal leading-normal mt-2 mb-2">
                    Cookies
                </h2>
                <p>
                    Ce site web utilise des cookies uniquement à des fins techniques (pour maintenir l'utilisateur connecté pendant l'utilisation du site web et stocker d'éventuelles préférences sur ce site web).<br />
                    Aucun cookie tiers n'est utilisé (et aucun cookie lié à la publicité, à l'analyse ni aux réseaux sociaux).
                </p>
                <h2 class="text-xl font-normal leading-normal mt-2 mb-2">
                    Code source
                </h2>
                <p>
                    Le code source de cette application est disponible sur <a href="https://github.com/carsso/kantine" class="underline hover:text-indigo-600" target="_blank"><i class="fab fa-github"></i> GitHub</a><br />
                    Le code source est sous licence <a href="https://opensource.org/licenses/MIT" class="underline hover:text-indigo-600" target="_blank">MIT</a>.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
