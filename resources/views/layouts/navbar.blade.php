<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center text-white fw-bold" href="{{ route('dashboard') }}">
            <img src="{{ asset('images/logo_bn.png') }}" alt="Bluenova Logo"
                style="height: 55px; width: auto; margin-right: 12px;">
        </a>


        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link text-white" href="{{ route('productos.index') }}"><i class="fa-solid fa-box"></i> Productos</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="{{ route('compras.index') }}"><i class="fa-solid fa-cart-arrow-down"></i> Compras</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="{{ route('ventas.index') }}"><i class="fa-solid fa-cash-register"></i> Ventas</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="{{ route('vendedores.index') }}"><i class="fa-solid fa-user-tie"></i>Vendedores</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="{{ route('cuentas.index') }}"><i class="fa-solid fa-cash-register"></i> Cuentas y Saldos</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="{{ route('caja.index') }}"><i class="fa-solid fa-cash-register"></i> Caja</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="{{ route('reportes.index') }}"><i class="fa-solid fa-chart-line"></i> Reportes</a></li>
                <!--<li class="nav-item"><a class="nav-link text-white" href="{{ route('cotizacion.index') }}"><i class="fa-solid fa-dollar-sign"></i> Cotización</a></li> -->
            </ul>

            <ul class="navbar-nav">
                @auth
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fa-solid fa-user"></i> {{ Auth::user()->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Perfil</a></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">Cerrar sesión</button>
                            </form>
                        </li>
                    </ul>
                </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>