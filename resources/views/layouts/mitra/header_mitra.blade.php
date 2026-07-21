<!-- Import font Poppins dari Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<div style="padding:10px 20px; font-family: 'Poppins', sans-serif;">
    <!-- Logo Luwina & Logo TA -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <img src="{{ asset('assets/luwina_logo.png') }}" alt="Logo Luwina" style="height:50px;">
        <img src="{{ asset('assets/telkomakses_logo.png') }}" alt="Telkom Akses" style="height:70px;">
    </div>

    <!-- Profil + NIK/NAMA -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:30px;">

        <div style="display:flex; align-items:center;">
            <img src="{{ asset('assets/profile.png') }}" alt="User Avatar"
                style="height:60px; border-radius:50%; margin-right:12px;">

            <div>
                <div style="font-weight:400; color:#133995; margin-bottom:4px;">
                    {{ $user['nik'] }}
                </div>

                <div style="color:#133995; font-weight:400;">
                    {{ $user['nama'] }}
                </div>
            </div>
        </div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn-logout">
                Logout
            </button>
        </form>

    </div>
</div>

<!-- Menu Navigasi -->
<div class="menu-nav">
    <a href="{{ route('mitra.makeproject') }}"
        class="{{ request()->routeIs('mitra.makeproject*') ? 'active' : '' }}"><span>MAKE PROJECT</span></a>
    <a href="{{ route('mitra.allproject') }}"
        class="{{ request()->routeIs('mitra.allproject*') ? 'active' : '' }}"><span>ALL PROJECT</span></a>
    <a href="{{ route('mitra.process') }}"
        class="{{ request()->routeIs('mitra.process*') ? 'active' : '' }}"><span>PROCESS</span></a>
    <a href="{{ route('mitra.acc') }}"
        class="{{ request()->routeIs('mitra.acc*') ? 'active' : '' }}"><span>ACC</span></a>
    <a href="{{ route('mitra.reject') }}"
        class="{{ request()->routeIs('mitra.reject*') ? 'active' : '' }}"><span>REJECT</span></a>
</div>


<style>
    .menu-nav {
        font-family: 'Poppins', sans-serif;
        display: flex;
        border: 1px solid #133995;
        border-radius: 15px;
        overflow: hidden;
        margin: 15px 20px;
        background-color: #F5F5F6;
    }

    .menu-nav a {
        flex: 1;
        text-align: center;
        padding: 15px 0;
        text-decoration: none;
        color: #133995;
        font-weight: 600;
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;
    }

    /* Lingkar biru di tengah */
    .menu-nav a::before {
        content: "";
        position: absolute;
        width: 80%;
        height: 70%;
        background-color: #133995;
        border-radius: 20px;
        opacity: 0;
        transition: 0.2s;
        z-index: 0;
    }

    /* Saat aktif, munculkan pill biru */
    .menu-nav a.active::before {
        opacity: 1;
    }

    /* Supaya teks di atas pill */
    .menu-nav a span {
        position: relative;
        z-index: 1;
    }

    /* Warna teks saat aktif */
    .menu-nav a.active span {
        color: white;
    }

    .btn-logout {
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        background: #C8170D;
        color: #fff;
        padding: 10px 18px;
        border-radius: 8px;
    }

    .btn-logout:hover {
        color: #C8170D;
        background: #fff;
        border: 1px solid #C8170D;
    }
</style>

<script>
    document.querySelectorAll('.menu-nav a').forEach(link => {
        link.innerHTML = `<span>${link.textContent}</span>`;
        link.addEventListener('click', e => {
            document.querySelectorAll('.menu-nav a').forEach(link => {
                link.innerHTML = `<span>${link.textContent}</span>`;
            });
        });
    });
</script>

<script>
    document.getElementById('logoutForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        try {
            await firebase.auth().signOut();
        } catch (err) {
            console.log(err);
        }

        this.submit();
    });
</script>
