// import '@/css/adminlte.css'
import "../../css/adminlte.css"

import { Head, usePage } from '@inertiajs/react'
import { useEffect, useState } from 'react'
import brand from '../brand'
import { router } from '@inertiajs/react'
import GlobalLoader from '@/Components/GlobalLoader'

export default function AdminLayout({ title, children }) {
  const { url } = usePage()
  const { auth } = usePage().props
  const jamKerjaOpen = url.startsWith('/jam-kerja')
  const [isJamKerjaOpen, setIsJamKerjaOpen] = useState(jamKerjaOpen)

  useEffect(() => {
    setIsJamKerjaOpen(jamKerjaOpen)
  }, [jamKerjaOpen])

  useEffect(() => {
    const fixLayout = () => {
      if (window.$ && window.$.AdminLTE && window.$.AdminLTE.layout) {
        window.$.AdminLTE.layout.fix()
      }
    }
    fixLayout()
    setTimeout(fixLayout, 300)
  }, [])

  return (
    <div className="wrapper">
      <Head title={`${title} | ${brand.appName}`} />

      <header className="main-header">
        <a href="/" className="logo" style={{ background: brand.primaryColor }}>
          <span className="logo-mini">
            <img src={brand.logo} alt="logo" style={{ height: '25px' }} />
          </span>
          <span className="logo-lg">
            <img src={brand.logo} alt="logo" style={{ height: '30px', marginRight: '8px' }} />
            {brand.shortName}
          </span>
        </a>

        <nav className="navbar navbar-static-top" style={{ background: brand.primaryColor }}>
          <a href="#" className="sidebar-toggle" data-toggle="push-menu" role="button">
            <span className="sr-only">Toggle navigation</span>
          </a>
          <span className="navbar-brand">{brand.appName}</span>
        </nav>
      </header>

      <aside className="main-sidebar">
        <section className="sidebar">
          <div className="user-panel">
            <div className="pull-left image">
              <img src={brand.avatar} className="img-circle" alt="Campus Logo" />
            </div>
            <div className="pull-left info">
              {/* <p>{brand.campusName}</p> */}

              <p className="hidden-xs">
                {auth.user?.name || 'Guest'}
              </p>

              <div
                style={{
                  display: 'flex',
                  justifyContent: 'space-between',
                  alignItems: 'center',
                  fontSize: '12px',
                }}
              >
                <span>{auth.user?.role || 'User'}&nbsp;</span>
                <a
                  href="#"
                  onClick={(e) => {
                    e.preventDefault()
                    router.post(route('logout'))
                  }}
                  style={{
                    color: '#dd4b39',
                    textDecoration: 'none',
                  }}
                >
                  [logout]
                </a>
              </div>

            </div>
          </div>

          <ul className="sidebar-menu" data-widget="tree">
            {/* HEADER MENU */}
            <li className="header">MENU</li>

            <li className={url.startsWith('/dashboard') ? 'active' : ''}>
              <a href="/dashboard">
                <i className="fa fa-dashboard"></i>
                <span>Dashboard</span>
              </a>
            </li>

            <li>
              <a href="/rekap-bulanan">
                <i className="fa fa-line-chart"></i>
                <span>Rekap Bulanan</span>
              </a>
            </li>

            <li>
              <a href="/absensi-harian">
                <i className="fa fa-calendar-check-o"></i>
                <span>Absensi Harian</span>
              </a>
            </li>

            <li className={`treeview ${isJamKerjaOpen ? 'active menu-open' : ''}`}>
              <a
                href="#"
                onClick={(e) => {
                  e.preventDefault()
                  setIsJamKerjaOpen((prev) => !prev)
                }}
              >
                <i className="fa fa-clock-o"></i>
                <span>Jam Kerja</span>
                <span className="pull-right-container">
                  <i className="fa fa-angle-left pull-right"></i>
                </span>
              </a>
              <ul
                className="treeview-menu"
                style={{ display: isJamKerjaOpen ? 'block' : 'none' }}
              >
                <li className={url.startsWith('/jam-kerja/dinas') ? 'active' : ''}>
                  <a href="/jam-kerja/dinas">
                    <i className="fa fa-circle-o"></i>
                    Jadwal Dinas
                  </a>
                </li>
                <li className={url.startsWith('/jam-kerja/unit') ? 'active' : ''}>
                  <a href="/jam-kerja/unit">
                    <i className="fa fa-circle-o"></i>
                    Jadwal Unit
                  </a>
                </li>
                <li className={url.startsWith('/jam-kerja/sub-unit') ? 'active' : ''}>
                  <a href="/jam-kerja/sub-unit">
                    <i className="fa fa-circle-o"></i>
                    Jadwal Sub Unit
                  </a>
                </li>
                <li className={url.startsWith('/jam-kerja/pegawai') ? 'active' : ''}>
                  <a href="/jam-kerja/pegawai">
                    <i className="fa fa-circle-o"></i>
                    Jadwal Pegawai
                  </a>
                </li>
                <li className={url.startsWith('/jam-kerja/preview') ? 'active' : ''}>
                  <a href="/jam-kerja/preview">
                    <i className="fa fa-circle-o"></i>
                    Preview Resolver
                  </a>
                </li>
              </ul>
            </li>

            <li>
              <a href="/pegawai">
                <i className="fa fa-users"></i>
                <span>Pegawai</span>
              </a>
            </li>

            <li>
              <a href="/v1/pegawai">
                <i className="fa fa-users"></i>
                <span>Pegawai v1</span>
              </a>
            </li>

            {/* HEADER ADMIN */}
            <li className="header">ADMIN</li>

            <li>
              <a href="/user">
                <i className="fa fa-user"></i>
                <span>User</span>
              </a>
            </li>

            <li>
              <a href="/data-mesin-absen">
                <i className="fa fa-database"></i>
                <span>Data Mesin Absen</span>
              </a>
            </li>

            <li>
              <a href="/hari-libur-nasional">
                <i className="fa fa-calendar"></i>
                <span>Hari Libur Nasional</span>
              </a>
            </li>

            <li>
              <a href="/device">
                <i className="fa fa-tablet"></i>
                <span>Device</span>
              </a>
            </li>

            <li className={url.startsWith('/unit') ? 'active' : ''}>
              <a href="/unit">
                <i className="fa fa-building"></i> <span>Unit</span>
              </a>
            </li>

            <li className={url.startsWith('/subunit') ? 'active' : ''}>
              <a href="/subunit">
                <i className="fa fa-sitemap"></i> <span>Sub Unit</span>
              </a>
            </li>


          </ul>



        </section>
      </aside>

      <div className="content-wrapper">

        <section className="content-header">
          <h1>
            {title}
            <small>{brand.campusName}</small>
          </h1>

          <ol className="breadcrumb">
            <li><a href="/"><i className="fa fa-dashboard"></i> Home</a></li>
            <li className="active">{title}</li>
          </ol>
        </section>

        <section className="content">
          {children}
        </section>
      </div>

      <footer className="main-footer">
        <div className="pull-right hidden-xs">
          <b>Version</b> {brand.version}
        </div>
        <strong>{brand.footerText}</strong>
      </footer>

      <GlobalLoader />

    </div>
  )
}
