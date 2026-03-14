import AdminLayout from '@/Layouts/AdminLayout'
import { router } from '@inertiajs/react'
import { useEffect, useState } from 'react'

import Lightbox from 'yet-another-react-lightbox'
import 'yet-another-react-lightbox/styles.css'

export default function Index({ pegawais, units, subUnits, filters, stats }) {

    const fotoUrl = (id) => id ? `/pegawai/foto/${id}` : '/images/no-image.png'
    const absensiFotoIn = (sum_id) => sum_id ? `/absensi/foto/in/${sum_id}` : '/images/no-tap.png'
    const absensiFotoOut = (sum_id) => sum_id ? `/absensi/foto/out/${sum_id}` : '/images/no-tap.png'

    // =======================
    // STATE
    // =======================
    const [unit, setUnit] = useState(filters.unit_id || '')
    const [subUnit, setSubUnit] = useState(filters.sub_unit_id || '')
    const [date, setDate] = useState(filters.date || '')
    const [search, setSearch] = useState(filters.search || '')

    const [lightboxOpen, setLightboxOpen] = useState(false)
    const [lightboxImage, setLightboxImage] = useState(null)

    // =======================
    // SYNC STATE ← PROPS
    // =======================
    useEffect(() => {
        setUnit(filters.unit_id || '')
        setSubUnit(filters.sub_unit_id || '')
        setDate(filters.date || '')
        setSearch(filters.search || '')
    }, [filters])

    // =======================
    // HELPER: REQUEST FILTER
    // =======================
    const applyFilter = (params) => {
        router.get(
            route('absensi-harian.index'),
            {
                unit_id: params.unit_id ?? unit,
                sub_unit_id: params.sub_unit_id ?? subUnit,
                date: params.date ?? date,
                search: params.search ?? search,
            },
            {
                replace: true,
                preserveState: false,
            }
        )
    }

    // =======================
    // SUBMIT (OPTIONAL)
    // =======================
    const submitFilter = (e) => {
        e.preventDefault()
        applyFilter({})
    }

    const getStatusTextClass = (status) => {
        if (!status) return 'text-muted'

        if (status === 'ALPA') return 'text-red'
        if (status === 'HADIR') return 'text-green'

        // CT, DL, CKAP, IB, dll
        return 'text-orange'
    }

    const getMasukTooltip = (summary) => {
        if (!summary) return null

        if (
            summary.status_masuk === 'Adm' &&
            summary.time_in_final &&
            summary.time_in &&
            summary.time_in_final === summary.time_in
        ) {
            return 'Jam mesin (status administratif)'
        }

        return null
    }


    return (
        <AdminLayout title="Absensi Harian">

            {/* ================= FILTER ================= */}
            <div className="box">
                <form className="box-body" onSubmit={submitFilter}>
                    <div className="row">

                        {/* ===== UNIT ===== */}
                        <div className="col-md-3">
                            <label>Unit</label>
                            <select
                                className="form-control"
                                value={unit}
                                onChange={(e) => {
                                    const val = e.target.value
                                    setUnit(val)
                                    setSubUnit('')
                                    applyFilter({ unit_id: val, sub_unit_id: '' })
                                }}
                            >
                                <option value="">- Semua Unit -</option>
                                {units.map(u => (
                                    <option key={u.id} value={u.id}>
                                        {u.unit}
                                    </option>
                                ))}
                            </select>
                        </div>

                        {/* ===== SUB UNIT ===== */}
                        <div className="col-md-3">
                            <label>Sub Unit</label>
                            <select
                                className="form-control"
                                value={subUnit}
                                disabled={!unit}
                                onChange={(e) => {
                                    const val = e.target.value
                                    setSubUnit(val)
                                    applyFilter({ sub_unit_id: val })
                                }}
                            >
                                <option value="">- Semua Sub Unit -</option>
                                {subUnits.map(su => (
                                    <option key={su.id} value={su.id}>
                                        {su.sub_unit}
                                    </option>
                                ))}
                            </select>
                        </div>

                        {/* ===== TANGGAL ===== */}
                        <div className="col-md-2">
                            <label>Tanggal</label>
                            <input
                                type="date"
                                className="form-control"
                                value={date}
                                onChange={(e) => {
                                    const val = e.target.value
                                    setDate(val)
                                    applyFilter({ date: val })
                                }}
                            />
                        </div>

                        {/* ===== BUTTON ===== */}
                        <div className="col-md-1">
                            <label>&nbsp;</label>
                            <button className="btn btn-primary btn-block">
                                Show
                            </button>
                        </div>

                        {/* ===== SEARCH ===== */}
                        <div className="col-md-3">
                            <label>&nbsp;</label>
                            <input
                                className="form-control"
                                placeholder="Cari NIK / NIP / Nama"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                onKeyDown={(e) => {
                                    if (e.key === 'Enter') {
                                        applyFilter({ search })
                                    }
                                }}
                            />
                        </div>

                    </div>
                </form>
            </div>

            {/* ================= TABLE ================= */}
            <div className="box">
                <div className="box-body table-responsive">
                    <table className="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style={{ width: 260 }}>IDENTITY</th>
                                <th style={{ width: 138 }}>REF</th>
                                <th style={{ width: 188 }}>MASUK</th>
                                <th style={{ width: 188 }}>PULANG</th>
                                <th>KET</th>
                            </tr>
                        </thead>

                        <tbody>
                            {pegawais.data.map(p => (
                                <tr key={p.id}>

                                    {/* ===== IDENTITY ===== */}
                                    <td>
                                        <strong>{p.nama}</strong><br />
                                        <small><em>{p.active_history?.unit?.unit || '-'}</em></small><br />
                                        <small><em>{p.active_history?.sub_unit?.sub_unit || '-'}</em></small><br />
                                        Nik: {p.nik}<br />
                                        Nip: {p.nip || '-'}
                                    </td>

                                    {/* ===== REF ===== */}
                                    <td>

                                        <a href={fotoUrl(p.id)} data-lightbox="pegawai">
                                            <img
                                                src={fotoUrl(p.id)}
                                                onError={(e) => e.target.src = '/images/no-image.png'}
                                                style={{ width: 55, height: 65, border: '1px solid #ddd' }}
                                            />
                                        </a>

                                        <div style={{ fontSize: 12, marginTop: 6 }}>
                                            {filters.date}
                                        </div>
                                        <span>
                                            <a
                                                href="#"
                                                data-status={p.summary?.status_hari_final}
                                                className={`status label label-default ${getStatusTextClass(p.summary?.status_hari_final)}`}
                                                data-title="Hari"
                                                data-type="daily"
                                                data-nama={p.nama}
                                                data-nip={p.nip}
                                                data-nik={p.nik}
                                                data-date={filters.date}
                                                onClick={(e) => e.preventDefault()}
                                            >
                                                {p.summary?.status_hari_final ?? '-'}
                                            </a>
                                        </span>

                                    </td>

                                    {/* ===== MASUK ===== */}
                                    <td>

                                        <a href={absensiFotoIn(p.summary?.id)} data-lightbox="pegawai">
                                            <img
                                                src={absensiFotoIn(p.summary?.id)}
                                                onError={(e) => e.target.src = '/images/no-tap.png'}
                                                style={{ width: 55, height: 65, border: '1px solid #ddd' }}
                                            />
                                        </a>

                                        <div>

                                            <span
                                                style={{ fontSize: 12 }}
                                                title={getMasukTooltip(p.summary) || ''}
                                            >
                                                {p.summary?.time_in_final ?? '-'}
                                                {getMasukTooltip(p.summary) && (
                                                    <i
                                                        className="fa fa-info-circle text-muted"
                                                        style={{ marginLeft: 4 }}
                                                    ></i>
                                                )}
                                            </span>

                                            {p.summary?.status_masuk && p.summary.status_masuk !== 'SESUAI' && (
                                                <>
                                                    &nbsp;/
                                                    <span>{p.summary.status_masuk}</span>
                                                </>
                                            )}
                                        </div>
                                        <div>
                                            <span>
                                                <a
                                                    href="#"
                                                    data-status={p.summary?.status_masuk_final}
                                                    className={`status label label-default ${getStatusTextClass(p.summary?.status_masuk_final)}`}
                                                    data-title="Masuk"
                                                    data-type="in"
                                                    data-nama={p.nama}
                                                    data-nip={p.nip}
                                                    data-nik={p.nik}
                                                    data-date={filters.date}
                                                    onClick={(e) => e.preventDefault()}
                                                >
                                                    {p.summary?.status_masuk_final ?? '-'}
                                                </a>
                                            </span>

                                            &nbsp;
                                            <span>
                                                <i className="fa fa-map-marker"></i> {p.summary?.device_desc_in || '-'}
                                            </span>
                                        </div>
                                    </td>

                                    {/* ===== PULANG ===== */}
                                    <td>
                                        <a href={absensiFotoOut(p.summary?.id)} data-lightbox="pegawai">
                                            <img
                                                src={absensiFotoOut(p.summary?.id)}
                                                onError={(e) => e.target.src = '/images/no-tap.png'}
                                                style={{ width: 55, height: 65, border: '1px solid #ddd' }}
                                            />
                                        </a>

                                        <div>
                                            <span style={{ fontSize: 12 }}>{p.summary?.time_out_final ?? '-'}</span>
                                            {p.summary?.status_pulang && p.summary.status_pulang !== 'SESUAI' && (
                                                <>
                                                    &nbsp;/
                                                    <span>{p.summary.status_pulang}</span>
                                                </>
                                            )}
                                        </div>

                                        <div>
                                            <span>
                                                <a
                                                    href="#"
                                                    data-status={p.summary?.status_pulang_final}
                                                    className={`status label label-default ${getStatusTextClass(p.summary?.status_pulang_final)}`}
                                                    data-title="Pulang"
                                                    data-type="out"
                                                    data-nama={p.nama}
                                                    data-nip={p.nip}
                                                    data-nik={p.nik}
                                                    data-date={filters.date}
                                                    onClick={(e) => e.preventDefault()}
                                                >
                                                    {p.summary?.status_pulang_final ?? '-'}
                                                </a>
                                            </span>

                                            &nbsp;
                                            <span>
                                                <i className="fa fa-map-marker"></i> {p.summary?.device_desc_out || '-'}
                                            </span>
                                        </div>

                                    </td>

                                    {/* ===== KET ===== */}
                                    <td>
                                        sum_id: {p.summary?.id}
                                    </td>
                                </tr>
                            ))}

                            {pegawais.data.length === 0 && (
                                <tr>
                                    <td colSpan="5" className="text-center text-muted">
                                        Tidak ada data pegawai
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>

                    <div style={{ height: 30 }}></div>

                    {/* ================= PAGINATION ================= */}
                    <div className="clearfix" style={{ display: 'flex', justifyContent: 'space-between' }}>
                        <span className="label label-warning">
                            {stats.from || 0}–{stats.to || 0} / {stats.total}
                        </span>

                        <div>
                            {pegawais.links.map((link, i) => (
                                <button
                                    key={i}
                                    className={`btn btn-xs ${link.active ? 'btn-primary' : 'btn-default'}`}
                                    disabled={!link.url}
                                    onClick={() =>
                                        router.get(link.url, {}, { preserveState: true })
                                    }
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ))}
                        </div>
                    </div>

                </div>
            </div>

        </AdminLayout >
    )
}
