import AdminLayout from '../Layouts/AdminLayout'
import { router } from '@inertiajs/react'
import { useState } from 'react'

function fmtPct(v) {
  if (v === null || v === undefined) return '-'
  return `${v}%`
}

function fmtDate(v) {
  if (!v) return '-'
  const s = String(v)
  return s.length >= 10 ? s.slice(0, 10) : s
}

export default function Dashboard({
  filters,
  units = [],
  subUnits = [],
  meta,
  kpi,
  anomali = [],
  status_chart = [],
  trend = [],
  unit_rank = [],
  resolver_cov = [],
}) {
  const [date, setDate] = useState(filters?.date || '')
  const [unitId, setUnitId] = useState(filters?.unit_id ?? '')
  const [subUnitId, setSubUnitId] = useState(filters?.sub_unit_id ?? '')

  function applyFilters() {
    router.get(
      '/dashboard',
      {
        date,
        unit_id: unitId || '',
        sub_unit_id: subUnitId || '',
      },
      { preserveState: true, replace: true }
    )
  }

  function resetFilters() {
    setDate(filters?.date || '')
    setUnitId('')
    setSubUnitId('')
    router.get('/dashboard', {}, { preserveState: true, replace: true })
  }

  return (
    <AdminLayout title="Dashboard">
      <div className="row">
        <div className="col-md-12">
          <div className="box">
            <div className="box-header">
              <h3 className="box-title">Filter</h3>
            </div>
            <div className="box-body">
              <div className="row">
                <div className="col-sm-3">
                  <label>Tanggal</label>
                  <input
                    type="text"
                    className="form-control"
                    placeholder="YYYY-MM-DD"
                    value={date}
                    onChange={(e) => setDate(e.target.value)}
                  />
                </div>
                <div className="col-sm-3">
                  <label>Unit</label>
                  <select
                    className="form-control"
                    value={unitId}
                    onChange={(e) => {
                      const value = e.target.value
                      setUnitId(value)
                      setSubUnitId('')
                    }}
                  >
                    <option value="">Semua Unit</option>
                    {units.map((u) => (
                      <option key={u.id} value={u.id}>
                        {u.unit}
                      </option>
                    ))}
                  </select>
                </div>
                <div className="col-sm-3">
                  <label>Sub Unit</label>
                  <select
                    className="form-control"
                    value={subUnitId}
                    onChange={(e) => setSubUnitId(e.target.value)}
                    disabled={!unitId}
                  >
                    <option value="">Semua Sub Unit</option>
                    {subUnits.map((s) => (
                      <option key={s.id} value={s.id}>
                        {s.sub_unit}
                      </option>
                    ))}
                  </select>
                </div>
                <div className="col-sm-3" style={{ paddingTop: '25px' }}>
                  <button className="btn btn-primary btn-sm" onClick={applyFilters}>
                    Terapkan
                  </button>
                  <button
                    className="btn btn-default btn-sm"
                    style={{ marginLeft: '6px' }}
                    onClick={resetFilters}
                  >
                    Reset
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div className="row">
        <div className="col-md-12">
          <div className="box">
            <div className="box-header">
              <h3 className="box-title">Meta</h3>
            </div>
            <div className="box-body">
              <p><strong>Scope:</strong> {meta?.scope_label || '-'}</p>
              <p><strong>Last Updated:</strong> {fmtDate(meta?.last_updated)}</p>
            </div>
          </div>
        </div>
      </div>

      <div className="row">
        <div className="col-md-12">
          <div className="box">
            <div className="box-header">
              <h3 className="box-title">KPI</h3>
            </div>
            <div className="box-body table-responsive">
              <table className="table table-bordered">
                <tbody>
                  <tr>
                    <th>Total Active</th>
                    <td>{kpi?.total_active ?? 0}</td>
                    <th>Hadir</th>
                    <td>{kpi?.hadir ?? 0}</td>
                    <th>Telat</th>
                    <td>{kpi?.telat ?? 0}</td>
                  </tr>
                  <tr>
                    <th>Belum Masuk</th>
                    <td>{kpi?.belum_masuk ?? 0}</td>
                    <th>Belum Pulang</th>
                    <td>{kpi?.belum_pulang ?? 0}</td>
                    <th>Tidak Hadir</th>
                    <td>{kpi?.tidak_hadir ?? 0}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div className="row">
        <div className="col-md-6">
          <div className="box">
            <div className="box-header">
              <h3 className="box-title">Anomali</h3>
            </div>
            <div className="box-body table-responsive">
              <table className="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>NIK</th>
                    <th>Nama</th>
                    <th>Issue</th>
                    <th>Status Hari</th>
                  </tr>
                </thead>
                <tbody>
                  {anomali.map((r, i) => (
                    <tr key={`${r.nik}-${i}`}>
                      <td>{r.nik}</td>
                      <td>{r.nama || '-'}</td>
                      <td>{r.issue || '-'}</td>
                      <td>{r.status_hari || '-'}</td>
                    </tr>
                  ))}
                  {anomali.length === 0 && (
                    <tr>
                      <td colSpan="4" className="text-center text-muted">Tidak ada data</td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div className="col-md-6">
          <div className="box">
            <div className="box-header">
              <h3 className="box-title">Distribusi Status</h3>
            </div>
            <div className="box-body table-responsive">
              <table className="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>Status</th>
                    <th>Jumlah</th>
                  </tr>
                </thead>
                <tbody>
                  {status_chart.map((r, i) => (
                    <tr key={`${r.label}-${i}`}>
                      <td>{r.label}</td>
                      <td>{r.count}</td>
                    </tr>
                  ))}
                  {status_chart.length === 0 && (
                    <tr>
                      <td colSpan="2" className="text-center text-muted">Tidak ada data</td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div className="row">
        <div className="col-md-6">
          <div className="box">
            <div className="box-header">
              <h3 className="box-title">Trend</h3>
            </div>
            <div className="box-body table-responsive">
              <table className="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>Tanggal</th>
                    <th>Hadir %</th>
                    <th>Telat %</th>
                  </tr>
                </thead>
                <tbody>
                  {trend.map((r, i) => (
                    <tr key={`${r.date}-${i}`}>
                      <td>{fmtDate(r.date)}</td>
                      <td>{fmtPct(r.hadir_pct)}</td>
                      <td>{fmtPct(r.telat_pct)}</td>
                    </tr>
                  ))}
                  {trend.length === 0 && (
                    <tr>
                      <td colSpan="3" className="text-center text-muted">Tidak ada data</td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div className="col-md-6">
          <div className="box">
            <div className="box-header">
              <h3 className="box-title">Top Unit</h3>
            </div>
            <div className="box-body table-responsive">
              <table className="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>Unit</th>
                    <th>Score</th>
                  </tr>
                </thead>
                <tbody>
                  {unit_rank.map((r, i) => (
                    <tr key={`${r.unit_id}-${i}`}>
                      <td>{r.unit}</td>
                      <td>{fmtPct(r.score)}</td>
                    </tr>
                  ))}
                  {unit_rank.length === 0 && (
                    <tr>
                      <td colSpan="2" className="text-center text-muted">Tidak ada data</td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div className="row">
        <div className="col-md-12">
          <div className="box">
            <div className="box-header">
              <h3 className="box-title">Resolver Coverage</h3>
            </div>
            <div className="box-body table-responsive">
              <table className="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>Source</th>
                    <th>Count</th>
                  </tr>
                </thead>
                <tbody>
                  {resolver_cov.map((r, i) => (
                    <tr key={`${r.source}-${i}`}>
                      <td>{r.source}</td>
                      <td>{r.count}</td>
                    </tr>
                  ))}
                  {resolver_cov.length === 0 && (
                    <tr>
                      <td colSpan="2" className="text-center text-muted">Tidak ada data</td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </AdminLayout>
  )
}
