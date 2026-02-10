{{-- resources/views/frontend/home.blade.php (example path) --}}
@extends('backend.layouts.master')

@section('title', 'HOME | TRIMATRIC ARCHITECTS & ENGINEERS')

@section('content')
    @php
        $brandName = 'ArchReach';
        $companyId = null;
        $companySlug = null;
        $param = request()->route('company');

        if ($param instanceof \App\Models\SuperAdmin\GlobalSetup\Company) {
            $brandName = $param->name;
            $companyId = $param->id;
            $companySlug = $param->slug;
        } elseif (is_string($param) && $param !== '') {
            $row = \Illuminate\Support\Facades\DB::table('companies')
                ->where('slug', $param)->where('status', 1)->whereNull('deleted_at')->first();
            if ($row) {
                $brandName = $row->name;
                $companyId = $row->id;
                $companySlug = $row->slug;
            }
        }

        // Auth or forced user
        $forcedId = config('header.dev_force_user_id');
        $forcedId = is_numeric($forcedId) ? (int) $forcedId : null;
        $uid = \Illuminate\Support\Facades\Auth::id() ?? $forcedId;
        $isGuest = ($uid === null);

        // Defaults
        $canEditClient = false;
        $canEditOfficer = false;
        $canEditProfessional = false;
        $canEditEntrepreneur = false;
        $canEditEnterprise = false;

        if (!$isGuest && $companyId) {
            $user = \Illuminate\Support\Facades\DB::table('users')->where('id', $uid)->first();

            // Resolve user's company
            $userCompanyId = null;
            if ($user) {
                $userCompanyId = (int) ($user->company_id ?? 0);
                if (!$userCompanyId && !empty($user->role_id)) {
                    $userCompanyId = (int) \Illuminate\Support\Facades\DB::table('roles')
                        ->where('id', $user->role_id)->value('company_id');
                }
            }

            $userActive = $user && (int) ($user->status ?? 0) === 1;
            $inThisCompany = $userActive && $userCompanyId === (int) $companyId;

            if ($inThisCompany && \Illuminate\Support\Facades\Schema::hasTable('registration_master')) {
                // Client reg exists and not declined
                $hasClient = \Illuminate\Support\Facades\DB::table('registration_master')
                    ->where('user_id', $uid)
                    ->where('company_id', $companyId)
                    ->where('registration_type', 'client')
                    ->where(function ($q) {
                        $q->whereNull('approval_status')->orWhere('approval_status', '<>', 'declined');
                    })
                    ->exists();

                // Company officer reg exists and not declined
                $hasOfficer = \Illuminate\Support\Facades\DB::table('registration_master')
                    ->where('user_id', $uid)
                    ->where('company_id', $companyId)
                    ->where('registration_type', 'company_officer')
                    ->where(function ($q) {
                        $q->whereNull('approval_status')->orWhere('approval_status', '<>', 'declined');
                    })
                    ->exists();

                // Professional reg exists and not declined
                $hasProfessional = \Illuminate\Support\Facades\DB::table('registration_master')
                    ->where('user_id', $uid)
                    ->where('company_id', $companyId)
                    ->where('registration_type', 'professional')
                    ->where(function ($q) {
                        $q->whereNull('approval_status')->orWhere('approval_status', '<>', 'declined');
                    })
                    ->exists();

                // Entrepreneur reg exists and not declined
                $hasEntrepreneur = \Illuminate\Support\Facades\DB::table('registration_master')
                    ->where('user_id', $uid)
                    ->where('company_id', $companyId)
                    ->where('registration_type', 'entrepreneur')
                    ->where(function ($q) {
                        $q->whereNull('approval_status')->orWhere('approval_status', '<>', 'declined');
                    })
                    ->exists();

                // Enterprise reg exists and not declined
                $hasEnterprise = \Illuminate\Support\Facades\DB::table('registration_master')
                    ->where('user_id', $uid)
                    ->where('company_id', $companyId)
                    ->where('registration_type', 'enterprise_client')
                    ->where(function ($q) {
                        $q->whereNull('approval_status')->orWhere('approval_status', '<>', 'declined');
                    })
                    ->exists();

                $canEditClient = $hasClient;
                $canEditOfficer = $hasOfficer;
                $canEditProfessional = $hasProfessional;
                $canEditEntrepreneur = $hasEntrepreneur;
                $canEditEnterprise = $hasEnterprise;
            }
        }

        // Derive "hasRegistration" for existing UI logic
        $hasRegistration = (
            $canEditClient ||
            $canEditOfficer ||
            $canEditProfessional ||
            $canEditEntrepreneur ||
            $canEditEnterprise
        );

        $companyParam = $companySlug ?? $companyId;

        $regUrl = function (string $type) use ($companyParam) {
            switch ($type) {
                case 'client':
                    return $companyParam ? route('registration.client.create', ['company' => $companyParam]) : '#';
                case 'professional':
                    return $companyParam ? route('registration.professional.create', ['company' => $companyParam]) : '#';
                case 'entrepreneur':
                    return $companyParam ? route('registration.entrepreneur.step1.create', ['company' => $companyParam]) : '#';
                case 'enterprise_client':
                    return $companyParam ? route('registration.enterprise_client.step1.create', ['company' => $companyParam]) : '#';
                case 'company_officer':
                default:
                    return '#'; // JS handles company_officer key modal
            }
        };

        $editClientUrl = $companyParam ? route('registration.client.edit', ['company' => $companyParam]) : '#';
        $editOfficerUrl = $companyParam ? route('registration.company_officer.edit', ['company' => $companyParam]) : '#';
        $editProfessionalUrl = $companyParam ? route('registration.professional.edit', ['company' => $companyParam]) : '#';
        $editEntrepreneurUrl = $companyParam ? route('registration.entrepreneur.edit_entry.go', ['company' => $companyParam]) : '#';
        $editEnterpriseUrl = $companyParam ? route('registration.enterprise_client.edit_entry.go', ['company' => $companyParam]) : '#';

        // NEW: helper to centralize edit URLs used in the Blade below
        $editUrl = function (string $type) use (
            $editClientUrl,
            $editOfficerUrl,
            $editProfessionalUrl,
            $editEntrepreneurUrl,
            $editEnterpriseUrl
        ) {
            switch ($type) {
                case 'client':
                    return $editClientUrl;
                case 'company_officer':
                    return $editOfficerUrl;
                case 'professional':
                    return $editProfessionalUrl;
                case 'entrepreneur':
                    return $editEntrepreneurUrl;
                case 'enterprise_client':
                    return $editEnterpriseUrl;
                default:
                    return '#';
            }
        };

        $loginUrl = \Illuminate\Support\Facades\Route::has('company.login')
            ? route('company.login', ['company' => $companyParam])
            : '#';
    @endphp

    <!-- swiper css link -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

    <!-- font awesome cdn link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- Custom CSS embedded here -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600&display=swap');

        /* CSS starts here */
        :root {
            --main-color: #808080;
            --black: #222;
            --white: #fff;
            --light-black: #777;
            --light-white: rgba(255, 255, 255, 0.6);
            --dark-bg: rgba(0, 0, 0, 0.7);
            --light-bg: #eee;
            --border: .1rem solid var(--black);
            --box-shadow: 0 .5rem 1rem rgba(0, 0, 0, 0.5);
            --text-shadow: 0 1.5rem 3rem rgba(0, 0, 0, 0.3);
        }

        * {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            outline: none;
            border: none;
            text-decoration: none;
            text-transform: capitalize;
        }

        html {
            font-size: 80%;
            overflow-x: hidden;
            scroll-behavior: smooth;
        }

        html::-webkit-scrollbar {
            width: 1rem;
        }

        html::-webkit-scrollbar-track {
            background-color: var(--white);
        }

        html::-webkit-scrollbar-thumb {
            background-color: var(--main-color);
        }

        section {
            padding: 5rem 10%;
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
                transform: scale(.8);
            }
        }

        .heading {
            background-size: cover !important;
            background-position: center !important;
            padding-top: 10rem;
            padding-bottom: 15rem;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .heading h1 {
            font-size: 4rem;
            text-transform: uppercase;
            color: var(--white);
            text-shadow: var(--text-shadow);
        }

        .btn {
            display: inline-block;
            background: rgb(110, 199, 42);
            margin-top: 1rem;
            color: var(--white);
            font-size: 1.5rem;
            padding: 1rem 2rem;
            cursor: pointer;
            border-radius: 5px;
        }

        .btn:hover {
            background: whitesmoke;
            color: rgb(122, 185, 26);
            transition: .2s linear;
            border: 2px solid rgb(110, 199, 42);
        }

        .heading-title {
            text-align: center;
            margin-bottom: 3rem;
            font-size: 2.5rem;
            text-transform: uppercase;
            color: var(--black);
        }

        /* NOTE: You already have header from master layout. Keep this .header block if you use it elsewhere. */
        .header {
            position: sticky;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background-color: var(--white);
            display: flex;
            padding: 2rem 10%;
            box-shadow: var(--box-shadow);
            align-items: center;
            justify-content: space-between;
        }

        .header .logo {
            font-size: 2.5rem;
            color: var(--black);
        }

        .header .navbar a {
            font-size: 1.5rem;
            color: var(--black);
            margin-left: 3rem;
        }

        .header .navbar a:hover {
            color: rgb(120, 190, 8);
        }

        #menu-btn {
            font-size: 2.5rem;
            color: var(--black);
            cursor: pointer;
            display: none;
        }

        .home {
            padding: 0;
        }

        .home .slide {
            text-align: center;
            padding: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background-size: cover !important;
            background-position: center !important;
            min-height: 60rem;
            position: relative;
        }

        .home .slide::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: 1;
        }

        .home .slide .content {
            max-width: 100rem;
            position: relative;
            z-index: 2;
            display: none;
        }

        .home .swiper-slide-active .content {
            display: inline-block;
        }

        .home .slide .content span {
            display: block;
            font-size: 3rem;
            color: var(--white);
            padding-bottom: 1rem;
            font-weight: 600;
            animation: fadeIn .3s linear backwards .4s;
        }

        .home .slide .content h3 {
            font-size: 6vh;
            color: var(--white);
            text-transform: uppercase;
            line-height: 1.2;
            text-shadow: var(--text-shadow);
            padding: 1rem 0;
            animation: fadeIn .3s linear backwards .6s;
        }

        .home .slide .content .btn {
            animation: fadeIn .3s linear backwards .8s;
        }

        .home .swiper-button-next {
            right: inherit;
            top: inherit;
            bottom: 0;
            width: 5rem;
            height: 5rem;
            background: rgb(207, 207, 207);
            right: 0;
            border-radius: 50%;
            color: var(--black);
        }

        .home .swiper-button-prev {
            top: inherit;
            left: inherit;
            bottom: 0;
            left: 0;
            right: 0;
            width: 5rem;
            height: 5rem;
            background: rgb(175, 175, 175);
            color: var(--black);
            border-radius: 50%;
            z-index: 3;
        }

        .home .swiper-button-next:hover,
        .home .swiper-button-prev:hover {
            background: var(--main-color);
            color: var(--white);
        }

        .home .swiper-button-next::after,
        .home .swiper-button-prev::after {
            font-size: 2rem;
        }

        .home .swiper-button-prev {
            right: 5rem;
        }

        .services .box-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(16rem, 1fr));
            gap: 1.5rem;
        }

        .services .box-container .box {
            padding: 3rem 2rem;
            text-align: center;
            background: #3f3f3f;
            border-radius: .5rem;
            box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.8);
            transition: .2s linear;
            cursor: pointer;
        }

        .services .box-container .box img {
            width: 30%;
            height: 8rem;
            object-fit: contain;
        }

        .box p {
            color: #ffffff;
            padding: 0;
            font-size: 1 rem;
            /* equivalent to h4 */
        }

        .services .box-container .box:hover {
            background: whitesmoke;
            box-shadow: var(--box-shadow);
        }

        .services .box-container .box:hover h3 {
            color: rgb(110, 199, 42);
        }

        .services .box-container .box:hover p {
            color: #545454;
            padding: 0;
        }

        .box .btn {
            margin-top: 1rem;
            padding: 0.8rem 1.5rem;
            border: 2px solid rgb(255, 255, 255);
            background: #646464;
            color: rgb(255, 255, 255);
            border-radius: 8px;
            box-shadow: 0 5px 10px rgb(255, 255, 255);
        }

        .box .btn:hover {
            background: whitesmoke;
            color: rgb(0, 0, 0);
            border: 2px solid rgb(0, 0, 0);
            box-shadow: 0 5px 10px rgb(60, 60, 60);
        }

        .home-about {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 2rem;
        }

        .home-about .image {
            flex: 1 1 40rem;
        }

        .home-about .image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: .5rem;
        }

        .home-about .content {
            flex: 1 1 40rem;
            padding: 2rem;
            text-align: left;
        }

        .home-about .content h3 {
            font-size: 3rem;
            color: var(--black);
            padding-bottom: 1rem;
        }

        .home-about .content p {
            color: var(--light-black);
            font-size: 1.5rem;
            line-height: 2;
            padding-bottom: 1.5rem;
        }

        .home-about .content .btn {
            background: #3f3f3f;
            border: 2px solid #ffffff;
        }

        .home-about .content .btn:hover {
            background: #fff;
            border: 2px solid #26cc2b;
        }

        .home-packages {
            background: #f5f5f5;
            padding: 60px 5%;
            background: #ededed;
        }

        .home-packages .content p {
             color: #545454;
        }


        .heading-title {
            text-align: center;
            font-size: 3rem;
            margin-bottom: 40px;
        }

        .packagesSwiper {
            width: 100%;
            padding-bottom: 60px;
        }

        .swiper-wrapper {
            display: flex;
        }

        .swiper-slide {
            height: auto;
        }

        .box {
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 6px 18px rgba(0, 0, 0, .8);
        }

        .image img {
            width: 100%;
            height: 230px;
            object-fit: cover;
            padding: 5px;
            border-radius: 10px;
        }

        .content {
            padding: 20px;
            text-align: center;
        }

        .content h3 {
            font-size: 2rem;
        }

        .content h4 {
            font-size: 1.8rem;
            color: #717271;
            margin-bottom: 10px;
            font-weight: 300;
        }

        .content p {
            font-size: 1.2rem;
            margin-bottom: 15px;
        }

        .packages-next,
        .packages-prev {
            color: #626262;
        }

        .reviews {
            background: var(--light-bg);
            padding-bottom: 8rem;
        }

        .reviews .slide {
            padding: 2rem;
            border: 2px solid rgb(77, 77, 77);
            background: var(--white);
            text-align: center;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 1.0);
            transition: .2s linear;
            border-radius: 8px;
            user-select: none;
        }

        .reviews .slide .stars {
            padding-bottom: .5rem;
        }

        .reviews .slide .stars i {
            color: rgb(255, 200, 61);
            font-size: 2rem;
        }

        .reviews .slide h3 {
            font-size: 2rem;
            color: white;
        }

        .reviews .slide p {
            color: var(--light-black);
            font-size: 1.2rem;
            line-height: 2;
            padding: 1rem 0;
        }

        .reviews .slide span {
            font-size: 1.5rem;
            color: var(--main-color);
            display: block;
        }

        .reviews .slide img {
            width: 10rem;
            height: 10rem;
            border-radius: 50%;
            object-fit: cover;
            margin-top: 1rem;
        }

        .reviews .swiper-pagination {
            position: absolute;
            bottom: 2rem;
        }

        .reviews .swiper-pagination-bullet {
            background: var(--main-color);
            opacity: 0.5;
            width: 1rem;
            height: 1rem;
        }

        .reviews .swiper-pagination-bullet-active {
            background: var(--main-color);
            opacity: 1;
            width: 1.2rem;
            height: 1.2rem;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            line-height: 1.6;
        }

        .booking {
            padding: 2rem;
            background: none;
            max-width: 600px;
            margin: 50px auto;
            border-radius: 8px;
        }

        .heading-title {
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2.5rem;
            color: #26cc2b;
        }

        .heading-title::after {
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background: var(--primary-color);
            margin: 0.5rem auto 0;
            border-radius: 9999px;
        }

        .book-form {
            background: #eeeeee;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
        }

        .book-form .flex {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .inputBox span {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #555;
        }

        .inputBox input {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border: 1px solid #d2dbd1;
            font-size: 1rem;
            color: var(--text-color);
            transition: all 0.3s ease;
            outline: none;
        }

        .inputBox input:focus {
            border-color: rgb(28, 184, 28);
            box-shadow: 0 0 0 3px rgb(33, 191, 67);
        }

        .flex {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1.5rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .forgot-pass {
            color: var(--primary);
            text-decoration: none;
            font-size: 0.9rem;
        }

        .btn-form {
            display: block;
            width: 100%;
            padding: 8px;
            background: #4a4b4a;
            color: rgb(255, 255, 255);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.5rem;
            margin-top: 2rem;
            transition: 0.3s;
            text-align: center;
            text-decoration: none;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.5);
        }

        .btn-form:hover {
            background: #ffffff;
            border: 1px solid rgb(49, 215, 43);
            color: #26cc2b;
        }

        .social-signup {
            text-align: center;
            margin-top: 1rem;
            color: #1b9b1f;
            font-size: 15px;
        }

        .social-btns {
            width: 100%;
            display: block;
            gap: 15px;
        }

        .social-btns .btn {
            margin-top: 0;
            margin-bottom: 20px;
            background: #4a4b4a;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.5);
            padding: 8px;
        }

        .google {
            background: #ffffff;
        }

        .google:hover {
            background: #ffffff;
        }

        .facebook {
            background: var(--facebook);
        }

        .facebook:hover {
            background: #ffffff;
        }

        #banner {
            width: 100%;
            height: 300px;
            background: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            text-align: center;
        }

        .banner-text {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .banner-text h1 {
            font-size: 40px;
            font-family: sans-serif;
            font-weight: 600;
            color: rgb(88, 219, 36);
            white-space: nowrap;
        }

        .banner-text h1 {
            margin-top: 10px;
            width: 1200px;
            animation: move 16s linear infinite;
            cursor: pointer;
            transition: transform 8s reverse;
        }

        .banner-text h2 {
            font-size: 30px;
            text-decoration: underline;
            font-family: sans-serif;
            font-weight: 600;
            color: #5a5a5a;
        }

        @keyframes move {
            from {
                transform: translateX(-100%);
            }

            to {
                transform: translateX(100%);
            }
        }

        .banner-btn {
            margin: 30px auto 0;
        }

        .banner-btn a {
            width: 150px;
            text-decoration: none;
            display: inline-block;
            font-family: sans-serif;
            margin: 0 5px;
            padding: 15px 0;
            color: #131414;
            border: 1px solid rgb(78, 196, 31);
            border-radius: 8px;
            position: relative;
            z-index: 1;
            transition: color 0.5s;
            font-size: 15px;
            font-weight: 600;
        }

        .banner-btn a span {
            width: 0%;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
            background: rgb(88, 219, 36);
            z-index: -1;
            transition: 0.5s;
        }

        .banner-btn a:hover span {
            width: 100%;
        }

        .banner-btn a:hover {
            color: #040404;
        }

        .input[type="number"],
        input[type="date"],
        input[type="email"] {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
        }

        @media (min-width: 640px) {
            .book-form {
                padding: 3rem;
            }
        }

        .home-offer {
            background: white;
            padding: 8rem 5%;
            text-align: center;
            margin-top: 0%;
        }

        .home-offer .content {
            max-width: 50rem;
            margin: auto;
            text-align: center;
        }

        .home-offer .content h3 {
            font-size: 4rem;
            color: rgb(88, 219, 36);
            text-transform: uppercase;
        }

        .home-offer .content p {
            font-size: 1.5rem;
            color: var(--light-black);
            line-height: 2;
            padding: 1.5rem 0;
        }

        .home-offer .content .btn {
            margin-top: 1rem;
            background: #4a4b4a;
            border-radius: 8px;
        }

        .home-offer .content .btn:hover {
            background: whitesmoke;
            color: rgb(88, 219, 36);
            border: 2px solid rgb(88, 219, 36);
        }

        .footer {
            background: #545454;
            padding: 60px 5% 20px;
            color: #e2e2e2;
        }

        .footer .box-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 2fr));
            gap: 30px;
        }

        .footer .box-container .box {
            text-align: left;
            padding: 20px;
            background: #3f3f3f;
            border: none;
        }

        .footer .box h3 {
            font-size: 1.8rem;
            margin-bottom: 15px;
            margin-left: 5px;
            color: #ffffff;
            text-transform: capitalize;
        }

        .footer .box a {
            display: block;
            font-size: 1.4rem;
            color: #ffffff;
            padding: 8px 0;
            text-decoration: none;
            transition: 0.3s;
        }

        .footer .box a i {
            color: #4edf25;
            margin-right: 8px;
            size: 1.6rem;
        }

        .footer .box a:hover {
            color: #fff;
            padding-left: 5px;
        }

        .footer .box p {
            font-size: 1.4rem;
            color: #ccc;
            line-height: 2;
        }

        .footer .credit {
            text-align: center;
            margin-top: 40px;
            font-size: 1.4rem;
            color: #ccc;
        }

        .footer .credit span {
            color: #e53935;
            margin-left: 5px;
        }
        #footer-email {
            text-transform: none !important;
        }
        @media (max-width: 768px) {
            .footer {
                text-align: center;
            }

            .footer .box a {
                justify-content: center;
            }
        }

        @media (max-width: 1200px) {
            section {
                padding: 3rem 5%;
            }
        }

        @media (max-width: 991px) {
            html {
                font-size: 55%;
            }

            section {
                padding: 3rem 2rem;
            }

            .home .slide .content h3 {
                font-size: 4rem;
            }

            .header {
                padding: 2rem;
            }
        }

        @media (max-width: 768px) {
            .home .slide .content h3 {
                font-size: 3.5rem;
            }
        }

        @media (max-width: 600px) {
            html {
                font-size: 50%;
            }

            .home .slide .content h3 {
                font-size: 3.2rem;
            }
        }

        @media (max-width: 500px) {
            html {
                font-size: 3rem;
            }
        }

        @media (max-width: 480px) {
            html {
                font-size: 48%;
            }
        }

        @media (max-width: 460px) {
            .home .slide .content h3 {
                font-size: 2.8rem;
            }
        }

        @media (max-width: 450px) {
            html {
                font-size: 50%;
            }

            .heading-title {
                font-size: 2rem;
            }
        }

        @media (max-width: 350px) {
            .home .slide .content h3 {
                font-size: 3rem;
            }
        }

        @media (max-width: 280px) {
            html {
                font-size: 45%;
            }
        }

    .guest-register {
        opacity: 0.6;
        pointer-events: auto;     /* allow click */
        cursor: pointer;          /* show clickable */
        }

    /* Prevent browser from re-focusing disabled Register buttons */
    #registration-types .btn.disabled {
        pointer-events: auto !important; /* allow click */
        user-select: none;
        outline: none !important;
    }

    #registration-types .btn.disabled:focus {
        outline: none !important;
        box-shadow: none !important;
    }
    </style>
<section>
<div class="container py-4">
    <h1 class="h4 mb-3">Entrepreneur Dashboard</h1>
    <p class="text-muted mb-4">Role type: {{ $roleType ?? 'Entrepreneur' }}</p>
    {{-- Client-facing widgets / requisitions overview --}}
</div>
</section>
<section id="home" class="home">

    <div class="swiper home-slider">
        <div class="swiper-wrapper">

            <div class="swiper-slide slide"
                 style="background: url('{{ asset('assets/images/Build 1.jpg') }}') no-repeat center/cover;">
                <div class="content">
                    <span>Building, Construction</span>
                    <h3>we build your dream</h3>
                    <a href="#" class="btn">learn more</a>
                </div>
            </div>

            <div class="swiper-slide slide"
                 style="background: url('{{ asset('assets/images/Architecture.jpg') }}') no-repeat center/cover;">
                <div class="content">
                    <span>architectural, design, concept</span>
                    <h3>futurestic design and concept</h3>
                    <a href="#" class="btn">discover more</a>
                </div>
            </div>

            <div class="swiper-slide slide"
                 style="background: url('{{ asset('assets/images/Interior1.jpg') }}') no-repeat center/cover;">
                <div class="content">
                    <span>exterior, interior, ideas</span>
                    <h3>make your home studio</h3>
                    <a href="#" class="btn">discover more</a>
                </div>
            </div>

            <div class="swiper-slide slide"
                 style="background: url('{{ asset('assets/images/Furniture L1.jpg') }}') no-repeat center/cover;">
                <div class="content">
                    <span>furniture, design, concept</span>
                    <h3>Luxurious living</h3>
                    <a href="#" class="btn">discover more</a>
                </div>
            </div>

            <div class="swiper-slide slide"
                 style="background: url('{{ asset('assets/images/Kitchen.jpg') }}') no-repeat center/cover;">
                <div class="content">
                    <span>kitchen, concept, healthy</span>
                    <h3>future healthy kitchen</h3>
                    <a href="#" class="btn">discover more</a>
                </div>
            </div>

            <div class="swiper-slide slide"
                 style="background: url('{{ asset('assets/images/Arch D1.jpg') }}') no-repeat center/cover;">
                <div class="content">
                    <span>structure, strength, future</span>
                    <h3>make your dream worthwhile</h3>
                    <a href="#" class="btn">discover more</a>
                </div>
            </div>

        </div>

        <!-- Correct swiper navigation, only one set -->
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </div>

</section>


    <!--- services section starts-->

    <!-- OUR SERVICES -->
    <section id="services" class="services">
        <h1 class="heading-title">Our Services</h1>

        <style>
            /* Ensures uniform, attractive images in each card */
            #services .box img {
                width: 100%;
                height: 220px;
                /* fixed equal height */
                object-fit: cover;
                border-radius: 10px 10px 0 0;
            }

            /* Add spacing between image and text */
            #services .box h3,
            #services .box p {
                margin-top: 12px;
                /* creates the gap you want */
            }

            /* Optional: add padding inside card for cleaner spacing */
            #services .box {
                padding: 10px 12px 20px;
            }

            .reg-types .box img {
                width: 100%;
                height: 220px;
                object-fit: cover;
                border-radius: 10px 10px 0 0;
            }
        </style>

        <div class="box-container">

            <div class="box">
                <img src="{{ asset('assets/images/Our_Services/Interior.jpg') }}" alt="Interior Design">
                <h3 style="color: #39FF14;">Interior</h3>
                <p>Premium interior design services delivering customized, elegant, and functional spaces with complete
                    material planning and execution.</p>
                <p>Turnkey interior solutions delivering functional, aesthetic, and durable spaces.</p>
            </div>

            <div class="box">
                <img src="{{ asset('assets/images/Our_Services/Architecture.jpg') }}" alt="Architectural Design">
                <h3 style="color: #39FF14;">Architectural</h3>
                <p>Innovative and functional architectural designs crafted to enhance lifestyle, maximize space efficiency,
                    and ensure long-lasting value.</p>
                <p>Comprehensive architectural solutions from concept to detailed design.</p>
            </div>

            <div class="box">
                <img src="{{ asset('assets/images/Our_Services/Material Supply.jpg') }}" alt="Material Supply">
                <h3 style="color: #39FF14;">Material Supply</h3>
                <p>Trusted supplier of high-quality interior and architectural materials, ensuring timely delivery for
                    homes, offices, and commercial projects.</p>
                <p>Supplying high-quality, Imported, certified materials to meet project standards.</p>
            </div>

            <div class="box">
                <img src="{{ asset('assets/images/Our_Services/Construction.jpg') }}" alt="Construction Services">
                <h3 style="color: #39FF14;">Construction</h3>
                <p>Reliable, quality-driven construction services with strong project management, ensuring timely delivery
                    and structural integrity.</p>
                <p>Comprehensive construction solutions ensuring quality, durability, and timely delivery.</p>
            </div>

            <div class="box">
                <img src="{{ asset('assets/images/Our_Services/Structural.jpg') }}" alt="Structural Engineering">
                <h3 style="color: #39FF14;">Structural</h3>
                <p>Advanced structural engineering solutions designed for safety, strength, and resilience using
                    industry-grade analysis techniques.</p>
                <p>Structural design and execution focused on safety, durability, and compliance.</p>
            </div>

        </div>
    </section>

    <!-- services section ends -->

    <!-- about section starts -->
    <section id="about" class="home-about">
        <div class="image">
            <img src="{{ asset('assets/images/build3.jpg') }}" alt="About Us">
        </div>
        <div class="content">
            <h1 class="heading-title">About Us</h1>

            <p>
                TRIMATRIC Architects & Engineers is a multidisciplinary design firm established in 2007,
                delivering innovative Architectural, Interior, and Turnkey solutions across Bangladesh.
                We transform ideas into inspiring spaces by blending creativity, functionality, and
                technical excellence.
            </p>

            <p>
                Guided by the belief “Innovation we create, excellence we believe,” our team is dedicated
                to providing thoughtful designs, quality service, and long-term value to our clients.
                From elegant homes to large commercial developments, we strive to create environments
                that enrich lives and stand the test of time.
            </p>

            <a href="#about" class="btn">Read More</a>
        </div>

    </section>
    <!-- about section ends -->

    <!-- entrepreneurs section starts -->
    <section id="entrepreneurs" class="home-about" style="background:#f9f9f9;">
        <div class="image">
            <!-- Temporary Internet Image (replace later with asset) -->
            <img src="https://images.unsplash.com/photo-1521737711867-e3b97375f902?q=80&w=1200"
                alt="We Are Hiring Entrepreneurs">
        </div>

        <div class="content">
            <h1 class="heading-title">We Are Hiring Entrepreneurs</h1>

            <p>
                To expand our support across Bangladesh, TRIMATRIC is inviting motivated
                <strong>Remote Entrepreneurs</strong> from all districts to collaborate with us.
                Your role will be to collect and manage interior, architectural, and material-supply
                related client requisitions and help execute projects under our expert supervision.
            </p>

            <p>
                This initiative opens a promising pathway for new entrepreneurs to grow their business
                using our powerful ERP platform designed for requisition management, approvals, task
                assignment, follow-up tracking, payment integration, and project commissioning — all
                through a secure and fully customized dashboard.
            </p>

            <p>
                Simply log in and complete your registration. We will review your profile, evaluate your
                skills and readiness, and contact you immediately. Training and guidance will be provided
                whenever necessary to ensure your long-term success.
            </p>

            <a href="#registration-types" class="btn">Register Now</a>
        </div>
    </section>
    <!-- entrepreneurs section ends -->

    <!-- packages section starts -->
    <section id="packages" class="home-packages">
        <h1 class="heading-title">Our Projects</h1>

        <div class="swiper packagesSwiper">
            <div class="swiper-wrapper box-container">
                <div class="swiper-slide box">
                    <div class="image">
                        <img src="{{ asset('assets/images/Our_Projects/1. Alisha Mart.webp') }}" alt="BG">
                    </div>
                    <div class="content">
                        <h3>Alesha Mart</h3>
                        <h4>Tezgaon, Dhaka</h4>
                        <p>Design-Build Turnkey Interior, Civil, Plumbing, Sanitary, Furniture Supply Works.</p>
                        <a href="#booking" class="btn">learn-more</a>
                    </div>
                </div>

                <div class="swiper-slide box">
                    <div class="image">
                        <img src="{{ asset('assets/images/Our_Projects/2. Ollyo.webp') }}" alt="TM">
                    </div>
                    <div class="content">
                        <h3>Ollyo</h3>
                        <h4>Dumni, Dhaka</h4>
                        <p>Design-Build Turnkey Construction, Exterior, Civil, Landscape, Plumbing, Sanitary Works, Electrical, Interior, Imported Furniture Supply.</p>
                        <a href="#booking" class="btn">learn more</a>
                    </div>
                </div>

                <div class="swiper-slide box">
                    <div class="image">
                        <img src="{{ asset('assets/images/Our_Projects/3. EGMCL.webp') }}" alt="TM">
                    </div>
                    <div class="content">
                        <h3>EGMCL</h3>
                        <h4>AEPZ, Narayangonj</h4>
                        <p>Construction, Interior, Furniture Supply - Design-Build Interior Renovation, Branding, All-Kinds of Supply Items.</p>
                        <a href="#booking" class="btn">learn more</a>
                    </div>
                </div>

                <div class="swiper-slide box">
                    <div class="image">
                        <img src="{{ asset('assets/images/Our_Projects/4. Indochine.webp') }}" alt="TM">
                    </div>
                    <div class="content">
                        <h3>Indochine</h3>
                        <h4>Shajadpur, Dhaka</h4>
                        <p>Design-Build Turnkey Interior & Furniture Supply.</p>
                        <a href="#booking" class="btn">learn more</a>
                    </div>
                </div>

                <div class="swiper-slide box">
                    <div class="image">
                        <img src="{{ asset('assets/images/Our_Projects/5. Hams garments.webp') }}" alt="TM">
                    </div>
                    <div class="content">
                        <h3>Hams garments</h3>
                        <h4>Niketan, Dhaka</h4>
                        <p>Design-Build Turnkey Interior & Furniture Supply.</p>
                        <a href="#booking" class="btn">learn more</a>
                    </div>
                </div>

                <div class="swiper-slide box">
                    <div class="image">
                        <img src="{{ asset('assets/images/Our_Projects/6. Lecture Publications.webp') }}" alt="England">
                    </div>
                    <div class="content">
                        <h3>Lecture Publications</h3>
                        <h4>Fakirapool, Dhaka</h4>
                        <p>Design-Build Turnkey Interior & Furniture Supply.</p>
                        <a href="#booking" class="btn">learn-more</a>
                    </div>
                </div>

                <div class="swiper-slide box">
                    <div class="image">
                        <img src="{{ asset('assets/images/Our_Projects/7. Mollica Inn.webp') }}" alt="England">
                    </div>
                    <div class="content">
                        <h3>Mollica Inn</h3>
                        <h4>Nawgaon</h4>
                        <p>Design-Build Turnkey Exterior, Interior & Furniture Supply.</p>
                        <a href="#booking" class="btn">learn-more</a>
                    </div>
                </div>

                <div class="swiper-slide box">
                    <div class="image">
                        <img src="{{ asset('assets/images/Our_Projects/8. Singapore Bangkok Market.webp') }}" alt="England">
                    </div>
                    <div class="content">
                        <h3>Singapore Bangkok Market</h3>
                        <h4>Chittagong</h4>
                        <p>Exterior, Interior, Civil, Lift Supply, Electrical - Furniture Supply.</p>
                        <a href="#booking" class="btn">learn-more</a>
                    </div>
                </div>

                <div class="swiper-slide box">
                    <div class="image">
                        <img src="{{ asset('assets/images/Our_Projects/9. Stella Showroom.webp') }}" alt="England">
                    </div>
                    <div class="content">
                        <h3>Stella Showroom</h3>
                        <h4>Banglamotor, Dhaka</h4>
                        <p>Design-Build Turnkey Interior & Furniture Supply.</p>
                        <a href="#booking" class="btn">learn-more</a>
                    </div>
                </div>
                <div class="swiper-slide box">
                    <div class="image">
                        <img src="{{ asset('assets/images/Our_Projects/10. Abul Khair Mosque.webp') }}" alt="England">
                    </div>
                    <div class="content">
                        <h3>Abul Khair Mosque</h3>
                        <h4>Kaliyakoir, Gazipur</h4>
                        <p>Design-Build Turnkey Interior, and Marble Fitting-Fixing Works.</p>
                        <a href="#booking" class="btn">learn-more</a>
                    </div>
                </div>
                <div class="swiper-slide box">
                    <div class="image">
                        <img src="{{ asset('assets/images/Our_Projects/11. Shamima Residence.webp') }}" alt="England">
                    </div>
                    <div class="content">
                        <h3>Shamima Residence</h3>
                        <h4>Bailyroad, Dhaka</h4>
                        <p>Design-Build Turnkey Residential Interior & Furniture Supply.</p>
                        <a href="#booking" class="btn">learn-more</a>
                    </div>
                </div>
                <div class="swiper-slide box">
                    <div class="image">
                        <img src="{{ asset('assets/images/Our_Projects/12. Central hospital.webp') }}" alt="England">
                    </div>
                    <div class="content">
                        <h3>Central hospital</h3>
                        <h4>Shaymoli, Dhaka</h4>
                        <p>Design-Build Exterior & Interior Works.</p>
                        <a href="#booking" class="btn">learn-more</a>
                    </div>
                </div>
                <div class="swiper-slide box">
                    <div class="image">
                        <img src="{{ asset('assets/images/Our_Projects/13. Al-Noor Eye Hospital.webp') }}" alt="England">
                    </div>
                    <div class="content">
                        <h3>Al-Noor Eye Hospital</h3>
                        <h4>Rayerbazar, Dhaka</h4>
                        <p>Design-Consultancy Service, with Design-Build Interior & Furniture Supply Works.</p>
                        <a href="#booking" class="btn">learn-more</a>
                    </div>
                </div>

                <div class="swiper-slide box">
                    <div class="image">
                        <img src="{{ asset('assets/images/Our_Projects/14. Banasree Mosque.webp') }}" alt="England">
                    </div>
                    <div class="content">
                        <h3>Banasree Mosque</h3>
                        <h4>Banasree, Dhaka</h4>
                        <p>Design-Build Turnkey Interior Works.</p>
                        <a href="#booking" class="btn">learn-more</a>
                    </div>
                </div>

                <div class="swiper-slide box">
                    <div class="image">
                        <img src="{{ asset('assets/images/Our_Projects/15. Pan Pacific lounge.webp') }}" alt="England">
                    </div>
                    <div class="content">
                        <h3>Pan Pacific lounge</h3>
                        <h4>Dhanmondi, Dhaka</h4>
                        <p>Design-Build Turnkey Interior & Furniture Supply Works.</p>
                        <a href="#booking" class="btn">learn-more</a>
                    </div>
                </div>

            </div>

            <div class="swiper-button-next packages-next"></div>
            <div class="swiper-button-prev packages-prev"></div>
        </div>
    </section>
    <!--package section ends-->

    <!-- reviews section starts -->
    <section class="reviews">
        <h1 class="heading-title">Our Clients</h1>
        <div class="swiper reviews-slider">
            <div class="swiper-wrapper">

                <div class="swiper-slide slide">
                    <div class="stars">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                            class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p>“The institution turnkey interior project was delivered with complete excellence, from design to final execution. The work met our institutional standards for quality and functionality."</p>
                    <h3>Kawshar Ahmed</h3>
                    <span>Founder & CEO, Ollyo & JoomShaper</span>
                    <img src="{{ asset('assets/images/Our_Clients/Ollyo.jpg') }}" alt="AS">
                </div>

                <div class="swiper-slide slide">
                    <div class="stars">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                            class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p>“We are very satisfied with the quality and finishing of the interior work. The project was completed on time and exceeded our expectations.”</p>
                    <h3>SUMEDHA DE SILVA</h3>
                    <span>CEO, Bangladesh, Indochine International</span>
                    <img src="{{ asset('assets/images/Our_Clients/Indochine.jpg') }}" alt="Barbara Hope">
                </div>

                <div class="swiper-slide slide">
                    <div class="stars">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                            class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p>“The entire Turnkey Interior & Furniture Supply work was done with great care and professionalism. The material quality and detailing are truly impressive.”</p>
                    <h3>Abdullah</h3>
                    <span>Sr. Manager-HR & Admin, Newton/Lecture Publications</span>
                    <img src="{{ asset('assets/images/Our_Clients/Lecture Publications.jpg') }}" alt="Scatler Johanson">
                </div>

                <div class="swiper-slide slide">
                    <div class="stars">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                            class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p>“The Design–Build turnkey work for the Institution exterior, interior, and furniture supply was delivered with excellent quality and professionalism. The final outcome enhanced both the aesthetics and guest experience, and the project was completed on schedule.”</p>
                    <h3>Mr. Nurul Amin</h3>
                    <span>Owner, Mollica Inn</span>
                    <img src="{{ asset('assets/images/Our_Clients/Mollika Inn.png') }}" alt="Lorence Cook">
                </div>

                <div class="swiper-slide slide">
                    <div class="stars">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                            class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p>“Our home interior turned out beautifully. The design is elegant, functional, and perfectly suits our lifestyle.Our family is extremely pleased with the interior work."</p>
                    <h3>Mrs. Dr. Shamima</h3>
                    <span>Client, Shamima Residence</span>
                    <img src="https://placehold.co/100x100/808080/ffffff?text=User5" alt="Domain Host">
                </div>

                <div class="swiper-slide slide">
                    <div class="stars">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                            class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p>“The design beautifully balances traditional Islamic elements with modern finishing. The community is very satisfied with the quality and overall look of the mosque interior.”</p>
                    <h3>Md. Jahir Uddin</h3>
                    <span>Secretary, Banasree Mosque</span>
                    <img src="https://placehold.co/100x100/808080/ffffff?text=User6" alt="Jan Doe">
                </div>
            </div>
            <div class="swiper-pagination"></div>
        </div>
    </section>
    <!-- reviews section ends -->

    <!--- Registration section starts-->
    <section id="registration-types" class="services  reg-types">
        <h1 class="heading-title">Types of Registration</h1>

        <div class="box-container">

            {{-- CLIENT --}}
            <div class="box">
                <img src="{{ asset('assets/images/Registration_Types/1. Clients.webp') }}" alt="Clients">
                <p><h3 style="color: #39FF14;">Clients</h3> </p>
                <p>Reliable platform for homeowners or individuals to submit interior or architectural requisitions and
                    receive expert support from Trimatric.</p>

                @if($isGuest)
                    <span class="btn guest-register" aria-disabled="true" tabindex="-1" title="Please login to register"
                        onclick="requireLogin()">Register Now</span>
                @elseif(!$hasRegistration)
                    <a class="btn" href="{{ $regUrl('client') }}">Register Now</a>
                @else
                    @if($canEditClient)
                        <a class="btn" href="{{ $editUrl('client') }}">Edit Registration</a>
                    @else
                        <span class="btn disabled" aria-disabled="true" tabindex="-1" title="Already registered">Register Now</span>
                    @endif
                @endif
            </div>

            {{-- ENTERPRISE CLIENT --}}
            <div class="box">
                <img src="{{ asset('assets/images/Registration_Types/2. Enterprise clients.webp') }}" alt="Enterprise Clients">
                <p><h3 style="color: #39FF14;">Enterprise Clients</h3></p>
                <p>Designed for corporate, commercial, and institutional clients who require structured project handling,
                    approvals, and premium support.</p>

                @if($isGuest)
                    <span class="btn guest-register" aria-disabled="true" tabindex="-1" title="Please login to register"
                        onclick="requireLogin()">Register Now</span>
                @elseif(!$hasRegistration)
                    <a class="btn" href="{{ $regUrl('enterprise_client') }}">Register Now</a>
                @else
                    @if($canEditEnterprise)
                        <a class="btn" href="{{ $editUrl('enterprise_client') }}">Edit Registration</a>
                    @else
                        <span class="btn disabled" aria-disabled="true" tabindex="-1" title="Already registered">Register Now</span>
                    @endif
                @endif
            </div>

            {{-- ENTREPRENEUR --}}
            <div class="box">
                <img src="{{ asset('assets/images/Registration_Types/3. Entrepreneur.webp') }}" alt="Entrepreneur">
                <p><h3 style="color: #39FF14;">Entrepreneur</h3></p>
                <p>Work with Trimatric as a remote entrepreneur by collecting client requisitions and managing project tasks
                    under our supervision. Be our Business Partner</p>

                @if($isGuest)
                    <span class="btn guest-register" aria-disabled="true" tabindex="-1" title="Please login to register"
                        onclick="requireLogin()">Register Now</span>
                @elseif(!$hasRegistration)
                    <a class="btn" href="{{ $regUrl('entrepreneur') }}">Register Now</a>
                @else
                    @if($canEditEntrepreneur)
                        <a class="btn" href="{{ $editUrl('entrepreneur') }}">Edit Registration</a>
                    @else
                        <span class="btn disabled" aria-disabled="true" tabindex="-1" title="Already registered">Register Now</span>
                    @endif
                @endif
            </div>

            {{-- PROFESSIONAL --}}
            <div class="box">
                <img src="{{ asset('assets/images/Registration_Types/4. Professional.webp') }}" alt="Professionals">
                <p><h3 style="color: #39FF14;">Professional</h3></p>
                <p>For architects, engineers, designers, and skilled experts who want to submit their Professional expertise
                    history and be considered for upcoming Trimatric projects.</p>

                @if($isGuest)
                    <span class="btn guest-register" aria-disabled="true" tabindex="-1" title="Please login to register"
                        onclick="requireLogin()">Register Now</span>
                @elseif(!$hasRegistration)
                    <a class="btn" href="{{ $regUrl('professional') }}">Register Now</a>
                @else
                    @if($canEditProfessional)
                        <a class="btn" href="{{ $editUrl('professional') }}">Edit Registration</a>
                    @else
                        <span class="btn disabled" aria-disabled="true" tabindex="-1" title="Already registered">Register Now</span>
                    @endif
                @endif
            </div>

            {{-- COMPANY OFFICER --}}
            <div class="box">
                <img src="{{ asset('assets/images/Registration_Types/5. Company Officer.webp') }}" alt="Kitchen">
                <p><h3 style="color: #39FF14;">Company Officer</h3></p>
                <p>Entrepreneurs selected as official partners by Head office must register here. Officers receive assigned
                    tasks, manage client requisitions, and work directly with Trimatric.</p>

                @if($isGuest)
                    <span class="btn guest-register" aria-disabled="true" tabindex="-1" title="Please login to register"
                        onclick="requireLogin()">Register Now</span>
                @elseif(!$hasRegistration)
                    <a class="btn" href="#" data-co-trigger="1"
                     data-co-trigger="1"
                     data-company="{{ $companyParam }}"
                    >Register Now</a>
                @else
                    @if($canEditOfficer)
                        <a class="btn" href="{{ $editUrl('company_officer') }}">Edit Registration</a>
                    @else
                        <span class="btn disabled" aria-disabled="true" tabindex="-1" title="Already registered">Register Now</span>
                    @endif
                @endif
            </div>

        </div>
    </section>

    <!-- Registration section ends -->

    <!-- work flow section starts -->
    <section id="banner">
        <div class="banner-text">
            <h2>Our Work-flow</h2>
            <h1>HOW YOU WILL ENGAGE WITH US</h1>
            <div class="banner-btn">
                <a href="#"><span></span>Sign-up</a>
                <a href="#"><span></span>Registration</a>
                <a href="#"><span></span>Requisition</a>
                <a href="#"><span></span>Tracking</a>
            </div>
        </div>
    </section>
    <!-- work flow section ends -->

    <!-- offer section starts -->
    <section class="home-offer">
        <div class="content">
            <h3>up to 50% off</h3>
            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Doloribus, voluptatum! Quisquam, cumque. Quod,
                asperiores.</p>
            <a href="#booking" class="btn">book now</a>
        </div>
    </section>
    <!-- offer section ends -->

    @push('styles')
    <style>
        /* FIX: Prevent Registration Type card images from shrinking when using id="registration-types" */
        #registration-types .box img {
            width: 100% !important;
            height: 220px !important;
            object-fit: cover !important;
            border-radius: 10px 10px 0 0 !important;
            display: block !important;
        }

        /* Optional: keep card visuals consistent across all screen sizes */
        #registration-types .box {
            padding: 10px 12px 20px !important;
            border-radius: 12px !important;
        }
    </style>
    @endpush

    <!--footer section starts-->
    <section id="contact" class="footer">
        <div class="box-container">
            <div class="box">
                <h3>quick links</h3>
                <a href="#home"><i class="fas fa-angle-right"></i>Home</a>
                <a href="#about"><i class="fas fa-angle-right"></i>About</a>
                <a href="#packages"><i class="fas fa-angle-right"></i>Services</a>
                <a href="#booking"><i class="fas fa-angle-right"></i>Contact</a>
            </div>

            <div class="box">
                <h3>extra links</h3>
                <a href="#"><i class="fas fa-angle-right"></i>ask question</a>
                <a href="#about"><i class="fas fa-angle-right"></i>about us</a>
                <a href="#"><i class="fas fa-angle-right"></i>privacy policy</a>
                <a href="#"><i class="fas fa-angle-right"></i>terms of use</a>
            </div>

            <div class="box">
                <h3>contact info</h3>
                <a href="#"><i class="fas fa-phone"></i> +880248321385</a>
                <a href="#"><i class="fas fa-phone"></i> +8801321153149</a>
                <a href="#" id="footer-email"><i class="fas fa-envelope"></i>info@trimatric.com</a>
                <a href="#"><i class="fas fa-map"></i> Mezonet Building, Boro Moghbazar, 125 Ramna Century Avenue, Dhaka-1217, Bangladesh</a>
            </div>

            <div class="box">
                <h3>follow us</h3>
                <a href="#"><i class="fab fa-facebook-f"></i> facebook</a>
                <a href="#"><i class="fab fa-twitter"></i> twitter</a>
                <a href="#"><i class="fab fa-instagram"></i> instagram</a>
                <a href="#"><i class="fab fa-linkedin"></i> linkedin</a>
            </div>
        </div>
        <div class="credit">created by<span>@Trimatric Architects & Engineers</span> | all rights reserved | 2025</div>
    </section>
    <!--footer section ends-->

    <!-- swiper js link -->

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <script>
        function requireLogin() {
            Swal.fire({
                title: "Please Login / Sign Up First",
                text: "You must be logged in before registration.",
                icon: "info",
                confirmButtonText: "OK",
                confirmButtonColor: "#28a745",
                customClass: {
                    popup: 'swal2-border-radius-lg'
                }
            }).then(() => {

                // Remove focus from the clicked element to stop scroll-jump
                document.activeElement?.blur();

                // Smooth scroll to top
                window.scrollTo({ top: 0, behavior: "smooth" });

                // OPTIONAL: focus login button if exists
                const loginBtn = document.getElementById('login-btn');
                if (loginBtn) {
                    setTimeout(() => loginBtn.focus(), 400);
                }
            });
        }


        // Attach requireLogin only to the disabled buttons inside Types of Registration section
        document.addEventListener("DOMContentLoaded", () => {
            // Target only the clickable guest buttons inside registration-types
            const guestBtns = document.querySelectorAll(
                "#registration-types .btn[onclick*='requireLogin']"
            );

            guestBtns.forEach(btn => {
                btn.addEventListener("click", function(event) {
                    event.preventDefault();   // block default anchor behavior
                    event.stopPropagation();  // block parent click
                    requireLogin();           // show message
                });
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const menuBtn = document.getElementById('menu-btn');
            const navbar = document.querySelector('.header .navbar');

            if (menuBtn && navbar) {
                menuBtn.onclick = () => {
                    menuBtn.classList.toggle('fa-times');
                    navbar.classList.toggle('active');
                };

                navbar.querySelectorAll('a').forEach(link => {
                    link.onclick = () => {
                        setTimeout(() => {
                            menuBtn.classList.remove('fa-times');
                            navbar.classList.remove('active');
                        }, 100);
                    };
                });
            }

            if (document.querySelector(".home-slider")) {
                new Swiper(".home-slider", {
                    loop: true,
                    grabCursor: true,
                    effect: "fade",
                    autoplay: { delay: 2000, disableOnInteraction: false },
                    navigation: { nextEl: ".swiper-button-next", prevEl: ".swiper-button-prev" },
                });
            }

            if (document.querySelector(".reviews-slider")) {
                new Swiper(".reviews-slider", {
                    loop: true,
                    grabCursor: true,
                    spaceBetween: 20,
                    autoHeight: true,
                    pagination: { el: ".reviews .swiper-pagination", clickable: true },
                    autoplay: { delay: 5000, disableOnInteraction: false },
                    breakpoints: {
                        640: { slidesPerView: 1 },
                        768: { slidesPerView: 2 },
                        1024: { slidesPerView: 3 },
                    },
                });
            }

            if (document.querySelector(".packagesSwiper")) {
                new Swiper(".packagesSwiper", {
                    loop: true,
                    spaceBetween: 30,
                    speed: 1200,
                    autoplay: { delay: 3000, disableOnInteraction: false },
                    navigation: { nextEl: ".packages-next", prevEl: ".packages-prev" },
                    breakpoints: {
                        0: { slidesPerView: 1 },
                        768: { slidesPerView: 2 },
                        1024: { slidesPerView: 3 },
                    },
                });
            }

            window.onscroll = () => {
                if (menuBtn && navbar) {
                    menuBtn.classList.remove('fa-times');
                    navbar.classList.remove('active');
                }
            };

        });

        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('.book-form');
            if (!form) return;

            form.addEventListener('submit', (e) => {
                const pass = document.getElementsByName('password')[0]?.value ?? '';
                const rePass = document.getElementsByName('re_password')[0]?.value ?? '';

                if (pass !== rePass) {
                    e.preventDefault();
                    alert("Passwords do not match! Please try again.");
                    return;
                }

                const phoneEl = document.getElementsByName('phone')[0];
                if (phoneEl) {
                    const phone = phoneEl.value || '';
                    if (phone.length < 10) {
                        e.preventDefault();
                        alert("Please enter a valid phone number.");
                        return;
                    }
                }
            });

            const googleBtn = document.querySelector('.google');
            if (googleBtn) {
                googleBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    alert("Redirecting to Google Login...");
                });
            }

            const fbBtn = document.querySelector('.facebook');
            if (fbBtn) {
                fbBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    alert("Redirecting to Facebook Login...");
                });
            }
        });
    </script>
@endsection
