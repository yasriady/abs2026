import AdminLayout from '@/Layouts/AdminLayout'
import { router } from '@inertiajs/react'
import { useState } from 'react'

export default function Index({ loaded, month, unitId, units, dates, rows, pagination }) {

  const [m, setM] = useState(month ?? '')
  const [selectedUnit, setSelectedUnit] = useState(unitId ?? '')

  function submit(e) {
    e.preventDefault()

    if (!m || !selectedUnit) return

    router.get(
      '/jam-kerja/preview-unit',
      {
        month: m,
        unit_id: selectedUnit
      },
      { replace: true, preserveScroll: true }
    )
  }

  function formatCell(val) {
    if (!val) return '-'

    if (!val.jam_masuk && !val.jam_pulang)
      return 'OFF'

    return `${val.jam_masuk?.slice(0, 5)}-${val.jam_pulang?.slice(0, 5)}`
  }

  function getSumberBadge(sumber) {
    const badges = {
      'pegawai': 'badge bg-primary',
      'sub_unit': 'badge bg-success',
      'unit': 'badge bg-warning text-dark',
      'dinas': 'badge bg-info'
    }
    return badges[sumber] || 'badge bg-secondary'
  }


  function gotoPage(page) {
    router.get(
      '/jam-kerja/preview-unit',
      {
        month: m,
        unit_id: selectedUnit,
        page: page
      },
      { replace: true, preserveScroll: true }
    )
  }

  return (
    <AdminLayout title="Preview Resolver Unit">

      <div className="box">

        {/* HEADER */}
        <div className="box-header">
          <h3 className="box-title">
            Preview Resolver Jadwal Pegawai
          </h3>
        </div>

        {/* FILTER */}
        <form onSubmit={submit} className="box-body border-bottom">
          <div className="row align-items-end">

            <div className="col-md-3">
              <label className="form-label">Unit</label>
              <select
                className="form-control"
                value={selectedUnit}
                onChange={e => setSelectedUnit(e.target.value)}
                required
              >
                <option value="">Pilih Unit</option>
                {units.map(unit => (
                  <option key={unit.id} value={unit.id}>
                    {unit.nama_unit}
                  </option>
                ))}
              </select>
            </div>

            <div className="col-md-3">
              <label className="form-label">Bulan</label>
              <input
                type="month"
                className="form-control"
                value={m}
                onChange={e => setM(e.target.value)}
                required
              />
            </div>

            <div className="col-md-2">
              <button
                className="btn btn-primary w-100"
                disabled={!m || !selectedUnit}
              >
                Preview
              </button>
            </div>

          </div>
        </form>

        {/* INFO STATUS */}
        <div className="box-body">

          {!loaded && (
            <div className="alert alert-info">
              Silakan pilih unit dan bulan lalu klik preview.
            </div>
          )}

          {loaded && rows.length === 0 && (
            <div className="alert alert-warning">
              Tidak ada data pegawai ditemukan untuk unit yang dipilih.
            </div>
          )}

          {loaded && rows.length > 0 && (
            <>
              {/* SUMMARY */}
              <div className="mb-2 text-muted">
                Menampilkan <b>{pagination.total}</b> pegawai
                | Halaman {pagination.current_page} / {pagination.last_page}
                | Bulan: {month}
              </div>

              {/* TABLE */}
              <div className="table-responsive">
                <table className="table table-bordered table-sm table-striped">

                  <thead className="table-light">
                    <tr>
                      <th style={{ minWidth: 120 }}>NIK</th>
                      <th style={{ minWidth: 200 }}>Nama</th>
                      <th style={{ minWidth: 100 }}>Unit/Sub Unit</th>

                      {dates.map(d => (
                        <th key={d} className="text-center">
                          {d}
                        </th>
                      ))}
                    </tr>
                  </thead>

                  <tbody>
                    {rows.map(p => {
                      // Cari nama unit dan sub unit dari data yang ada
                      const unitName = units.find(u => u.id === p.unit_id)?.nama_unit || '-';
                      const unitId = units.find(u => u.id === p.unit_id)?.id || '-';

                      return (
                        <tr key={p.id}>
                          <td>{p.nik}</td>
                          <td>{p.nama}</td>
                          <td>
                            <small>
                              {unitId}<br />
                              {p.sub_unit_id ? `Sub: ${p.sub_unit_id}` : ''}
                            </small>
                          </td>

                          {dates.map(d => {
                            const jadwal = p.jadwal[d];
                            return (
                              <td key={d} className="text-center">
                                {jadwal ? (
                                  <>
                                    <div>{formatCell(jadwal)}</div>
                                    <small className={getSumberBadge(jadwal.sumber)}>
                                      {jadwal.sumber}
                                    </small>
                                  </>
                                ) : '-'}
                              </td>
                            );
                          })}
                        </tr>
                      );
                    })}
                  </tbody>

                </table>

                {/* PAGINATION */}
                <div className="d-flex justify-content-center mt-3 gap-2">

                  <button
                    className="btn btn-sm btn-secondary"
                    disabled={pagination.current_page === 1}
                    onClick={() => gotoPage(pagination.current_page - 1)}
                  >
                    Prev
                  </button>

                  {Array.from({ length: pagination.last_page }, (_, i) => i + 1).map(p => (
                    <button
                      key={p}
                      className={`btn btn-sm ${p === pagination.current_page ? 'btn-primary' : 'btn-outline-primary'}`}
                      onClick={() => gotoPage(p)}
                    >
                      {p}
                    </button>
                  ))}

                  <button
                    className="btn btn-sm btn-secondary"
                    disabled={pagination.current_page === pagination.last_page}
                    onClick={() => gotoPage(pagination.current_page + 1)}
                  >
                    Next
                  </button>

                </div>


              </div>
            </>
          )}

        </div>
      </div>
    </AdminLayout>
  )
}