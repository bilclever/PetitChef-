@extends('layouts.app')

@section('content')
    @push('styles')
        <style>
            .landing-page {
                display: grid;
                gap: 20px;
            }

            .section-card {
                background: linear-gradient(180deg, rgba(253, 250, 245, 0.92), rgba(253, 250, 245, 0.76));
                border: 1px solid rgba(221, 216, 206, 0.86);
                border-radius: 24px;
                box-shadow: 0 18px 40px rgba(44, 44, 42, 0.08);
                overflow: hidden;
            }

            .section-title {
                margin: 0;
                font-family: 'Fraunces', serif;
                font-size: clamp(28px, 3.5vw, 40px);
                line-height: 1.08;
                letter-spacing: -0.03em;
            }

            .section-subtitle {
                margin: 8px 0 0;
                font-size: 15px;
                color: var(--mid-gray);
                max-width: 60ch;
            }

            .hero {
                padding: 30px;
                display: grid;
                grid-template-columns: 1.05fr .95fr;
                gap: 24px;
                align-items: center;
            }

            .hero-text {
                animation: heroFade .9s ease both;
                display: grid;
                gap: 16px;
            }

            .hero-kicker {
                width: fit-content;
                padding: 8px 12px;
                border-radius: 999px;
                background: rgba(194, 98, 63, 0.12);
                color: var(--terracotta-dark);
                text-transform: uppercase;
                letter-spacing: .09em;
                font-size: 11px;
                font-weight: 700;
            }

            .hero-title {
                margin: 0;
                font-family: 'Fraunces', serif;
                font-size: clamp(38px, 5vw, 62px);
                line-height: .96;
                letter-spacing: -0.04em;
                max-width: 12ch;
            }

            .hero-subtitle {
                margin: 0;
                font-size: 17px;
                color: var(--charcoal);
                max-width: 44ch;
            }

            .hero-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
            }

            .hero-image-wrap {
                position: relative;
                border-radius: 24px;
                overflow: hidden;
                min-height: 420px;
                box-shadow: 0 26px 50px rgba(44, 44, 42, 0.18);
                animation: heroFloat 6s ease-in-out infinite;
            }

            .hero-image-wrap::before {
                content: '';
                position: absolute;
                inset: 0;
                z-index: 2;
                background: linear-gradient(180deg, rgba(0, 0, 0, 0.08), rgba(0, 0, 0, 0.55));
            }

            .hero-image {
                width: 100%;
                height: 100%;
                min-height: 420px;
                object-fit: cover;
                transform: scale(1.06);
                transition: transform .45s ease;
            }

            .hero-image-wrap:hover .hero-image {
                transform: scale(1.12);
            }

            .hero-image-tag {
                position: absolute;
                z-index: 3;
                left: 18px;
                bottom: 18px;
                background: rgba(253, 250, 245, 0.92);
                color: var(--charcoal);
                border-radius: 14px;
                padding: 10px 12px;
                font-size: 12px;
                border: 1px solid rgba(221, 216, 206, 0.86);
            }

            .popular-section {
                padding: 26px;
            }

            .popular-grid {
                margin-top: 16px;
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 14px;
            }

            .dish-card {
                position: relative;
                border-radius: 18px;
                overflow: hidden;
                background: #fff;
                border: 1px solid rgba(221, 216, 206, 0.92);
                transition: transform .26s ease, box-shadow .26s ease;
            }

            .dish-card:hover {
                transform: translateY(-6px);
                box-shadow: 0 20px 35px rgba(194, 98, 63, 0.22);
            }

            .dish-media {
                height: 170px;
                overflow: hidden;
                position: relative;
            }

            .dish-media img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                transition: transform .4s ease;
            }

            .dish-card:hover .dish-media img {
                transform: scale(1.1);
            }

            .dish-badge {
                position: absolute;
                top: 10px;
                left: 10px;
                padding: 6px 10px;
                border-radius: 999px;
                background: rgba(0, 0, 0, 0.68);
                color: #fff;
                font-size: 11px;
                font-weight: 700;
            }

            .dish-body {
                padding: 12px;
                display: grid;
                gap: 6px;
            }

            .dish-name {
                margin: 0;
                font-size: 15px;
                font-weight: 700;
            }

            .dish-meta {
                display: flex;
                justify-content: space-between;
                align-items: center;
                font-size: 13px;
            }

            .dish-price {
                font-family: 'Fraunces', serif;
                color: var(--terracotta-dark);
                font-size: 20px;
                line-height: 1;
            }

            .dish-rating {
                color: #db8e2f;
                font-size: 12px;
                font-weight: 700;
            }

            .dish-add {
                opacity: 0;
                transform: translateY(8px);
                transition: all .22s ease;
            }

            .dish-card:hover .dish-add {
                opacity: 1;
                transform: translateY(0);
            }

            .chefs-section,
            .how-section,
            .preview-section,
            .reviews-section,
            .final-cta {
                padding: 26px;
            }

            .chefs-grid {
                margin-top: 16px;
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 14px;
            }

            .chef-card {
                background: #fff;
                border: 1px solid rgba(221, 216, 206, 0.92);
                border-radius: 18px;
                padding: 14px;
            }

            .chef-head {
                display: grid;
                grid-template-columns: 64px 1fr;
                gap: 12px;
                align-items: center;
            }

            .chef-photo {
                width: 64px;
                height: 64px;
                border-radius: 16px;
                object-fit: cover;
                border: 2px solid rgba(194, 98, 63, 0.24);
            }

            .chef-name {
                margin: 0;
                font-size: 16px;
                font-weight: 700;
            }

            .chef-speciality {
                margin: 2px 0 0;
                color: var(--mid-gray);
                font-size: 12px;
            }

            .chef-badges {
                margin-top: 12px;
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
            }

            .chef-badge {
                padding: 5px 9px;
                border-radius: 999px;
                font-size: 11px;
                font-weight: 700;
                border: 1px solid rgba(221, 216, 206, 0.92);
            }

            .chef-badge-live {
                background: rgba(107, 140, 110, 0.14);
                color: #3f6f42;
            }

            .chef-badge-orders {
                background: rgba(194, 98, 63, 0.12);
                color: var(--terracotta-dark);
            }

            .chef-footer {
                margin-top: 12px;
            }

            .steps-grid {
                margin-top: 18px;
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 16px;
                position: relative;
            }

            .steps-grid::before {
                content: '';
                position: absolute;
                top: 33px;
                left: 10%;
                right: 10%;
                height: 3px;
                background: linear-gradient(90deg, rgba(194, 98, 63, 0.28), rgba(194, 98, 63, 0.8), rgba(194, 98, 63, 0.28));
                animation: pulseLine 2.2s ease-in-out infinite;
            }

            .step-item {
                position: relative;
                z-index: 2;
                background: #fff;
                border: 1px solid rgba(221, 216, 206, 0.92);
                border-radius: 18px;
                padding: 16px;
                display: grid;
                gap: 10px;
            }

            .step-icon {
                width: 42px;
                height: 42px;
                border-radius: 14px;
                display: grid;
                place-items: center;
                color: var(--terracotta-dark);
                background: rgba(194, 98, 63, 0.12);
                box-shadow: 0 0 0 8px rgba(194, 98, 63, 0.08);
            }

            .step-item h3 {
                margin: 0;
                font-size: 16px;
            }

            .step-item p {
                margin: 0;
                font-size: 13px;
                color: var(--mid-gray);
            }

            .preview-grid {
                margin-top: 16px;
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 14px;
            }

            .preview-card {
                border-radius: 18px;
                border: 1px solid rgba(221, 216, 206, 0.92);
                background: #fff;
                overflow: hidden;
            }

            .preview-shot {
                height: 230px;
                object-fit: cover;
                width: 100%;
            }

            .preview-body {
                padding: 12px;
            }

            .preview-body h3 {
                margin: 0;
                font-size: 15px;
            }

            .preview-body p {
                margin: 4px 0 0;
                color: var(--mid-gray);
                font-size: 13px;
            }

            .reviews-grid {
                margin-top: 16px;
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 14px;
            }

            .review-card {
                background: #fff;
                border: 1px solid rgba(221, 216, 206, 0.92);
                border-radius: 18px;
                padding: 14px;
                display: grid;
                gap: 10px;
            }

            .review-head {
                display: flex;
                gap: 10px;
                align-items: center;
            }

            .review-photo {
                width: 46px;
                height: 46px;
                border-radius: 12px;
                object-fit: cover;
            }

            .review-name {
                margin: 0;
                font-weight: 700;
                font-size: 14px;
            }

            .review-stars {
                color: #db8e2f;
                font-size: 12px;
                letter-spacing: .08em;
            }

            .review-text {
                margin: 0;
                font-size: 13px;
                color: var(--mid-gray);
            }

            .final-cta {
                display: grid;
                grid-template-columns: 1fr auto;
                align-items: center;
                gap: 14px;
                background: linear-gradient(140deg, rgba(194, 98, 63, 0.94), rgba(160, 78, 48, 0.96));
                color: #fff;
                border-color: rgba(160, 78, 48, 0.35);
            }

            .final-cta h2 {
                margin: 0;
                font-family: 'Fraunces', serif;
                font-size: clamp(32px, 4vw, 46px);
                line-height: .95;
            }

            .final-cta .pc-btn {
                border-color: rgba(255, 255, 255, 0.24);
                color: #fff;
            }

            .final-cta .pc-btn-primary {
                border-color: #fff;
                background: #fff;
                color: var(--terracotta-dark);
            }

            @keyframes heroFade {
                from {
                    opacity: 0;
                    transform: translateY(16px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes heroFloat {
                0%,
                100% {
                    transform: translateY(0);
                }
                50% {
                    transform: translateY(-9px);
                }
            }

            @keyframes pulseLine {
                0%,
                100% {
                    opacity: .35;
                    transform: scaleX(.98);
                }
                50% {
                    opacity: .95;
                    transform: scaleX(1.02);
                }
            }

            @media (max-width: 1150px) {
                .popular-grid {
                    grid-template-columns: repeat(2, 1fr);
                }

                .preview-grid,
                .reviews-grid,
                .steps-grid,
                .chefs-grid {
                    grid-template-columns: 1fr;
                }

                .steps-grid::before {
                    display: none;
                }
            }

            @media (max-width: 880px) {
                .hero,
                .final-cta {
                    grid-template-columns: 1fr;
                }

                .hero-image,
                .hero-image-wrap {
                    min-height: 330px;
                }
            }

            @media (max-width: 640px) {
                .hero,
                .popular-section,
                .chefs-section,
                .how-section,
                .preview-section,
                .reviews-section,
                .final-cta {
                    padding: 18px;
                }

                .popular-grid {
                    grid-template-columns: 1fr;
                }

                .hero-title {
                    max-width: none;
                }
            }
        </style>
    @endpush

    <div class="landing-page">
        <section class="section-card hero" id="hero">
            <div class="hero-text">
                <span class="hero-kicker">Cuisine locale premium</span>
                <h1 class="hero-title">Commandez des plats faits maison d’exception</h1>
                <p class="hero-subtitle">Des cuisiniers locaux, des plats frais, livrés ou à récupérer.</p>
                <div class="hero-actions">
                    <a href="{{ route('register') }}" class="pc-btn pc-btn-primary">Commander maintenant</a>
                    <a href="#plats-populaires" class="pc-btn">Voir le menu</a>
                </div>
            </div>
            <div class="hero-image-wrap" aria-hidden="true">
                <img
                    class="hero-image"
                    src="https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=1400&q=80"
                    alt="Assiette gastronomique"
                >
                <div class="hero-image-tag">Plats frais préparés chaque jour</div>
            </div>
        </section>

        <section class="section-card popular-section" id="plats-populaires">
            <h2 class="section-title">Plats populaires</h2>
            <p class="section-subtitle">Les coups de coeur du moment, notés par les clients et prêts rapidement.</p>
            <div class="popular-grid">
                <article class="dish-card">
                    <div class="dish-media">
                        <img src="https://images.unsplash.com/photo-1512058564366-18510be2db19?auto=format&fit=crop&w=1000&q=80" alt="Poulet braisé">
                        <span class="dish-badge">🔥 Populaire</span>
                    </div>
                    <div class="dish-body">
                        <h3 class="dish-name">Poulet braisé signature</h3>
                        <div class="dish-meta">
                            <span class="dish-price">3 900 FCFA</span>
                            <span class="dish-rating">⭐ 4.9</span>
                        </div>
                        <a href="{{ route('register') }}" class="pc-btn pc-btn-primary dish-add">Ajouter</a>
                    </div>
                </article>

                <article class="dish-card">
                    <div class="dish-media">
                        <img src="https://images.unsplash.com/photo-1559847844-5315695dadae?auto=format&fit=crop&w=1000&q=80" alt="Bowl frais">
                        <span class="dish-badge">⚡ Rapide</span>
                    </div>
                    <div class="dish-body">
                        <h3 class="dish-name">Bowl fraîcheur du chef</h3>
                        <div class="dish-meta">
                            <span class="dish-price">2 800 FCFA</span>
                            <span class="dish-rating">⭐ 4.8</span>
                        </div>
                        <a href="{{ route('register') }}" class="pc-btn pc-btn-primary dish-add">Ajouter</a>
                    </div>
                </article>

                <article class="dish-card">
                    <div class="dish-media">
                        <img src="https://images.unsplash.com/photo-1574653853027-7daba4f4f03e?auto=format&fit=crop&w=1000&q=80" alt="Pâtes fraîches">
                        <span class="dish-badge">🔥 Populaire</span>
                    </div>
                    <div class="dish-body">
                        <h3 class="dish-name">Pâtes crème truffée</h3>
                        <div class="dish-meta">
                            <span class="dish-price">4 300 FCFA</span>
                            <span class="dish-rating">⭐ 4.7</span>
                        </div>
                        <a href="{{ route('register') }}" class="pc-btn pc-btn-primary dish-add">Ajouter</a>
                    </div>
                </article>

                <article class="dish-card">
                    <div class="dish-media">
                        <img src="https://images.unsplash.com/photo-1521389508051-d7ffb5dc8f70?auto=format&fit=crop&w=1000&q=80" alt="Dessert maison">
                        <span class="dish-badge">⚡ Rapide</span>
                    </div>
                    <div class="dish-body">
                        <h3 class="dish-name">Dessert cacao maison</h3>
                        <div class="dish-meta">
                            <span class="dish-price">1 900 FCFA</span>
                            <span class="dish-rating">⭐ 4.9</span>
                        </div>
                        <a href="{{ route('register') }}" class="pc-btn pc-btn-primary dish-add">Ajouter</a>
                    </div>
                </article>
            </div>
        </section>

        <section class="section-card chefs-section" id="cuisiniers">
            <h2 class="section-title">Nos cuisiniers</h2>
            <p class="section-subtitle">Un service humain, des profils vérifiés, des spécialités qui donnent confiance.</p>
            <div class="chefs-grid">
                <article class="chef-card">
                    <div class="chef-head">
                        <img class="chef-photo" src="https://images.unsplash.com/photo-1583394293214-28ded15ee548?auto=format&fit=crop&w=400&q=80" alt="Chef Mariam">
                        <div>
                            <h3 class="chef-name">Chef Mariam</h3>
                            <p class="chef-speciality">Spécialité: cuisine togolaise revisitée</p>
                            <p class="chef-speciality">⭐ 4.8</p>
                        </div>
                    </div>
                    <div class="chef-badges">
                        <span class="chef-badge chef-badge-live">Disponible maintenant</span>
                        <span class="chef-badge chef-badge-orders">1 240 commandes</span>
                    </div>
                    <div class="chef-footer">
                        <a href="{{ route('register') }}" class="pc-btn">Voir ses plats</a>
                    </div>
                </article>

                <article class="chef-card">
                    <div class="chef-head">
                        <img class="chef-photo" src="https://images.unsplash.com/photo-1607631568010-a87245c0daf8?auto=format&fit=crop&w=400&q=80" alt="Chef Koffi">
                        <div>
                            <h3 class="chef-name">Chef Koffi</h3>
                            <p class="chef-speciality">Spécialité: grillades et sauces maison</p>
                            <p class="chef-speciality">⭐ 4.8</p>
                        </div>
                    </div>
                    <div class="chef-badges">
                        <span class="chef-badge chef-badge-live">Disponible maintenant</span>
                        <span class="chef-badge chef-badge-orders">980 commandes</span>
                    </div>
                    <div class="chef-footer">
                        <a href="{{ route('register') }}" class="pc-btn">Voir ses plats</a>
                    </div>
                </article>

                <article class="chef-card">
                    <div class="chef-head">
                        <img class="chef-photo" src="https://images.unsplash.com/photo-1566554273541-37a9ca77b91f?auto=format&fit=crop&w=400&q=80" alt="Chef Afi">
                        <div>
                            <h3 class="chef-name">Chef Afi</h3>
                            <p class="chef-speciality">Spécialité: plats healthy & fusion</p>
                            <p class="chef-speciality">⭐ 4.9</p>
                        </div>
                    </div>
                    <div class="chef-badges">
                        <span class="chef-badge chef-badge-live">Disponible maintenant</span>
                        <span class="chef-badge chef-badge-orders">1 510 commandes</span>
                    </div>
                    <div class="chef-footer">
                        <a href="{{ route('register') }}" class="pc-btn">Voir ses plats</a>
                    </div>
                </article>
            </div>
        </section>

        <section class="section-card how-section" id="comment-ca-marche">
            <h2 class="section-title">Comment ça marche</h2>
            <p class="section-subtitle">Choisir, commander, recevoir: un parcours clair en trois étapes.</p>
            <div class="steps-grid">
                <article class="step-item">
                    <span class="step-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </span>
                    <h3>Choisir</h3>
                    <p>Parcourez les plats, comparez rapidement les notes et les prix.</p>
                </article>
                <article class="step-item">
                    <span class="step-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M6 6h15l-1.5 9h-12z"/><path d="M6 6 4 3H1"/><circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>
                    </span>
                    <h3>Commander</h3>
                    <p>Ajoutez au panier puis validez en quelques secondes depuis mobile ou desktop.</p>
                </article>
                <article class="step-item">
                    <span class="step-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </span>
                    <h3>Recevoir</h3>
                    <p>Suivez la préparation en temps réel jusqu’à la récupération ou livraison.</p>
                </article>
            </div>
        </section>

        <section class="section-card preview-section" id="experience">
            <h2 class="section-title">Expérience fluide sur toute l’app</h2>
            <p class="section-subtitle">Prévisualisez le parcours commande, suivi et panier.</p>
            <div class="preview-grid">
                <article class="preview-card">
                    <img class="preview-shot" src="https://images.unsplash.com/photo-1556745757-8d76bdb6984b?auto=format&fit=crop&w=900&q=80" alt="Ecran commande">
                    <div class="preview-body">
                        <h3>Ecran commande</h3>
                        <p>Navigation rapide, visuels plats et actions directes.</p>
                    </div>
                </article>

                <article class="preview-card">
                    <img class="preview-shot" src="https://images.unsplash.com/photo-1586953208448-b95a79798f07?auto=format&fit=crop&w=900&q=80" alt="Ecran suivi commande">
                    <div class="preview-body">
                        <h3>Ecran suivi commande</h3>
                        <p>Statuts actualisés et notifications de progression.</p>
                    </div>
                </article>

                <article class="preview-card">
                    <img class="preview-shot" src="https://images.unsplash.com/photo-1615719413546-198b25453f85?auto=format&fit=crop&w=900&q=80" alt="Ecran panier">
                    <div class="preview-body">
                        <h3>Panier intelligent</h3>
                        <p>Validation claire des quantités, prix et total avant paiement.</p>
                    </div>
                </article>
            </div>
        </section>

        <section class="section-card reviews-section" id="avis-clients">
            <h2 class="section-title">Avis clients</h2>
            <p class="section-subtitle">Ils commandent, reviennent et recommandent.</p>
            <div class="reviews-grid">
                <article class="review-card">
                    <div class="review-head">
                        <img class="review-photo" src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=260&q=80" alt="Cliente Nora">
                        <div>
                            <p class="review-name">Nora A.</p>
                            <div class="review-stars">⭐⭐⭐⭐⭐</div>
                        </div>
                    </div>
                    <p class="review-text">Service rapide et plats ultra frais. L’interface est super claire sur mobile.</p>
                </article>

                <article class="review-card">
                    <div class="review-head">
                        <img class="review-photo" src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=260&q=80" alt="Client Kevin">
                        <div>
                            <p class="review-name">Kevin D.</p>
                            <div class="review-stars">⭐⭐⭐⭐⭐</div>
                        </div>
                    </div>
                    <p class="review-text">Le suivi en temps réel est top. Je sais exactement quand récupérer ma commande.</p>
                </article>

                <article class="review-card">
                    <div class="review-head">
                        <img class="review-photo" src="https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=260&q=80" alt="Cliente Alice">
                        <div>
                            <p class="review-name">Alice K.</p>
                            <div class="review-stars">⭐⭐⭐⭐⭐</div>
                        </div>
                    </div>
                    <p class="review-text">Des cuisiniers fiables et des plats très bien notés. Ça donne envie de recommander.</p>
                </article>
            </div>
        </section>

        <section class="section-card final-cta" id="cta-final">
            <div>
                <h2>Prêt à commander ?</h2>
            </div>
            <div>
                <a href="{{ route('register') }}" class="pc-btn pc-btn-primary">Commander maintenant</a>
            </div>
        </section>
    </div>
@endsection
