import AdminLayout from '@/Layouts/AdminLayout'
import { router } from '@inertiajs/react'
import { useMemo, useState } from 'react'

function sourceLabel(source) {
  if (source === 'pegawai') return 'Jadwal Pegawai'
  if (source === 'sub_unit') return 'Jadwal Sub Unit'
  if (source === 'unit') return 'Jadwal Unit'
  if (source === 'dinas') return 'Jadwal Dinas'
  return source || '-'
}

function toHm(value) {
  if (!value) return '-'
  return String(value).slice(0, 5)
}

export default function Index({ filters, checked, day, pegawai, history, resolved, pegawaiHints }) {
  const [nik, setNik] = useState(filters.nik || '')
  const [date, setDate] = useState(filters.date || '')

  const hintMap = useMemo(() => {
    const map = {}
    ;(pegawaiHints || []).forEach((p) => {
      map[p.nik] = p
    })
    return map
  }, [pegawaiHints])

  function submit(e) {
    e.preventDefault()
    router.get(
      '/jam-kerja/preview',
      { nik, date },
      { preserveState: true, replace: true }
    )
  }

  function resetForm() {
    setNik('')
    setDate(filters.date || '')
    router.get('/jam-kerja/preview', {}, { preserveState: true, replace: true })
  }

  return (
    <AdminLayout title="Preview Resolver">
      <div className="box">
        <div className="box-header">
          <h3 className="box-title">Uji Resolver Jam Kerja</h3>
        </div>

        <form className="box-body" onSubmit={submit}>
          <div className="row">
            <div className="col-sm-4">
              <label>NIK</label>
              <input
                className="form-control"
                list="pegawai-hints-preview"
                placeholder="Masukkan NIK"
                value={nik}
                onChange={(e) => setNik(e.target.value)}
              />
              <datalist id="pegawai-hints-preview">
                {(pegawaiHints || []).map((p) => (
                  <option key={p.nik} value={p.nik}>
                    {p.nama} {p.nip ? `- ${p.nip}` : ''}
                  </option>
                ))}
              </datalist>
              {nik && hintMap[nik] && (
                <small className="text-muted">
                  {hintMap[nik].nama}
                  {hintMap[nik].nip ? ` (${hintMap[nik].nip})` : ''}
                </small>
              )}
            </div>
            <div className="col-sm-3">
              <label>Tanggal</label>
              <input
                type="date"
                className="form-control"
                value={date}
                onChange={(e) => setDate(e.target.value)}
              />
            </div>
            <div className="col-sm-5" style={{ paddingTop: '25px' }}>
              <button type="submit" className="btn btn-primary btn-sm">
                Cek Resolver
              </button>
              <button
                type="button"
                className="btn btn-default btn-sm"
                style={{ marginLeft: '6px' }}
                onClick={resetForm}
              >
                Reset
              </button>
            </div>
          </div>
        </form>
      </div>

      {checked && (
        <div className="box">
          <div className="box-header">
            <h3 className="box-title">Hasil Preview</h3>
          </div>
          <div className="box-body">
            {!pegawai && (
              <div className="alert alert-warning">
                NIK tidak ditemukan pada data master pegawai.
              </div>
            )}

            {pegawai && (
              <div className="table-responsive">
                <table className="table table-bordered">
                  <tbody>
                    <tr>
                      <th style={{ width: '240px' }}>Pegawai</th>
                      <td>{pegawai.nama || '-'} ({pegawai.nik})</td>
                    </tr>
                    <tr>
                      <th>NIP</th>
                      <td>{pegawai.nip || '-'}</td>
                    </tr>
                    <tr>
                      <th>Tanggal</th>
                      <td>{filters.date || '-'} ({day || '-'})</td>
                    </tr>
                    <tr>
                      <th>Unit Aktif</th>
                      <td>{history?.unit || '-'}</td>
                    </tr>
                    <tr>
                      <th>Sub Unit Aktif</th>
                      <td>{history?.sub_unit || '-'}</td>
                    </tr>
                    <tr>
                      <th>Periode Histori</th>
                      <td>
                        {history ? `${history.begin_date || '-'} s/d ${history.end_date || 'sekarang'}` : '-'}
                      </td>
                    </tr>
                    <tr>
                      <th>Sumber Resolver</th>
                      <td>
                        {resolved ? (
                          <span className="label label-success">{sourceLabel(resolved.sumber)}</span>
                        ) : (
                          <span className="label label-danger">Tidak ada jadwal ter-resolve</span>
                        )}
                      </td>
                    </tr>
                    <tr>
                      <th>Jam Masuk</th>
                      <td>{toHm(resolved?.jam_masuk)}</td>
                    </tr>
                    <tr>
                      <th>Jam Pulang</th>
                      <td>{toHm(resolved?.jam_pulang)}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            )}
          </div>
        </div>
      )}
    </AdminLayout>
  )
}
