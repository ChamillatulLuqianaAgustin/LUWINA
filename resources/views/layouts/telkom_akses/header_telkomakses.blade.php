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
                    <span id="userNik">Memuat...</span>
                </div>

                <div style="color:#133995; font-weight:400;">
                    <span id="userNama">Memuat...</span>
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
    <a href="{{ route('telkomakses.allproject') }}"
        class="{{ request()->routeIs('telkomakses.allproject*') ? 'active' : '' }}"><span>ALL PROJECT</span></a>
    <a href="{{ route('telkomakses.process') }}"
        class="{{ request()->routeIs('telkomakses.process*') ? 'active' : '' }}"><span>PROCESS</span></a>
    <a href="{{ route('telkomakses.acc') }}"
        class="{{ request()->routeIs('telkomakses.acc*') ? 'active' : '' }}"><span>ACC</span></a>
    <a href="{{ route('telkomakses.reject') }}"
        class="{{ request()->routeIs('telkomakses.reject*') ? 'active' : '' }}"><span>REJECT</span></a>
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

<script>
    document.addEventListener("DOMContentLoaded", function() {

        const nik = localStorage.getItem("user_nik");
        const nama = localStorage.getItem("user_nama");

        console.log("NIK:", nik);
        console.log("Nama:", nama);

        document.getElementById("userNik").textContent = nik || "-";
        document.getElementById("userNama").textContent = nama || "-";

    });
</script>
