<template>
  <div class="space-y-6">
    <!-- Statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Jobs en attente</div>
        <div class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ stats.pending }}</div>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Jobs réussis (total)</div>
        <div class="mt-2 text-2xl font-semibold text-green-600 dark:text-green-400">{{ stats.successful }}</div>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Jobs réussis (aujourd'hui)</div>
        <div class="mt-2 text-2xl font-semibold text-green-600 dark:text-green-400">{{ stats.successful_today }}</div>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Jobs échoués (total)</div>
        <div class="mt-2 text-2xl font-semibold text-red-600 dark:text-red-400">{{ stats.failed }}</div>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Jobs échoués (aujourd'hui)</div>
        <div class="mt-2 text-2xl font-semibold text-red-600 dark:text-red-400">{{ stats.failed_today }}</div>
      </div>
    </div>

    <!-- Jobs en cours d'exécution -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
      <div class="p-4">
        <h2 class="text-lg font-semibold mb-4">Jobs en cours d'exécution</h2>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Début</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">DisplayName</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Payload</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="job in pendingJobs" :key="job.id" class="bg-blue-50 dark:bg-blue-900/20">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ formatDate(job.created_at) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                  <div>{{ getDisplayName(job.payload) }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">Tenant: {{ getTenantName(job.payload) }}</div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                  <div class="relative">
                    <div class="flex justify-between items-center mb-2">
                      <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Payload:</div>
                      <button @click="togglePayload(job.id)" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-xs">
                        <i :class="expandedPayloads[job.id] ? 'fas fa-chevron-up' : 'fas fa-chevron-down'" class="mr-1"></i>
                        {{ expandedPayloads[job.id] ? 'Réduire' : 'Déployer' }}
                      </button>
                    </div>
                    <div v-if="!expandedPayloads[job.id]" class="text-xs">
                      {{ truncatePayload(job.payload) }}
                    </div>
                    <div v-else class="text-xs">
                      <pre class="bg-gray-50 dark:bg-gray-700 p-2 rounded overflow-x-auto whitespace-pre-wrap break-all">{{ formatPayload(job.payload) }}</pre>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Historique des jobs -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
      <div class="p-4">
        <h2 class="text-lg font-semibold mb-4">Derniers jobs</h2>
        <div class="overflow-x-auto">
          <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-48">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-48">DisplayName</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Détails</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="job in recentJobs" :key="job.id" :class="job.failed_at ? 'bg-red-50 dark:bg-red-900/20' : 'bg-green-50 dark:bg-green-900/20'">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ formatDate(job.failed_at || job.finished_at) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                  <div>{{ getDisplayName(job.payload) }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">Tenant: {{ getTenantName(job.payload) }}</div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                  <div class="space-y-4">
                    <!-- Payload -->
                    <div class="relative">
                      <div class="flex justify-between items-center mb-2">
                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Payload:</div>
                        <button @click="togglePayload(job.id)" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-xs">
                          <i :class="expandedPayloads[job.id] ? 'fas fa-chevron-up' : 'fas fa-chevron-down'" class="mr-1"></i>
                          {{ expandedPayloads[job.id] ? 'Réduire' : 'Déployer' }}
                        </button>
                      </div>
                      <div v-if="!expandedPayloads[job.id]" class="text-xs">
                        {{ truncatePayload(job.payload) }}
                      </div>
                      <div v-else class="text-xs">
                        <pre class="bg-gray-50 dark:bg-gray-700 p-2 rounded overflow-x-auto whitespace-pre-wrap break-all">{{ formatPayload(job.payload) }}</pre>
                      </div>
                    </div>

                    <!-- Status -->
                    <div class="relative">
                      <div class="flex justify-between items-center mb-2">
                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Statut:</div>
                        <button v-if="job.failed_at" @click="toggleException(job.id)" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-xs">
                          <i :class="expandedExceptions[job.id] ? 'fas fa-chevron-up' : 'fas fa-chevron-down'" class="mr-1"></i>
                          {{ expandedExceptions[job.id] ? 'Réduire' : 'Déployer' }}
                        </button>
                        <button v-else @click="toggleResult(job.id)" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-xs">
                          <i :class="expandedResults[job.id] ? 'fas fa-chevron-up' : 'fas fa-chevron-down'" class="mr-1"></i>
                          {{ expandedResults[job.id] ? 'Réduire' : 'Déployer' }}
                        </button>
                      </div>
                      <div v-if="job.failed_at" class="text-red-600 dark:text-red-400">
                        <div v-if="!expandedExceptions[job.id]" class="text-xs">
                          {{ truncateException(job.exception) }}
                        </div>
                        <div v-else class="text-xs">
                          <pre class="bg-gray-50 dark:bg-gray-700 p-2 rounded overflow-x-auto whitespace-pre-wrap break-all">{{ formatException(job.exception) }}</pre>
                          <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-2 mb-2">Logs:</div>
                          <pre class="bg-gray-50 dark:bg-gray-700 p-2 rounded overflow-x-auto whitespace-pre-wrap break-all">{{ formatResult(job.logs) }}</pre>
                        </div>
                      </div>
                      <div v-else class="text-green-600 dark:text-green-400">
                        <div v-if="!expandedResults[job.id]" class="text-xs">
                          {{ truncateResult(job.result) }}
                        </div>
                        <div v-else class="text-xs">
                          <pre class="bg-gray-50 dark:bg-gray-700 p-2 rounded overflow-x-auto whitespace-pre-wrap break-all">{{ formatResult(job.result) }}</pre>
                        </div>
                      </div>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'JobMonitor',
  data() {
    return {
      pendingJobs: [],
      failedJobs: [],
      successfulJobs: [],
      tenants: {},
      stats: {
        pending: 0,
        failed: 0,
        successful: 0,
        failed_today: 0,
        failed_week: 0,
        successful_today: 0,
        successful_week: 0
      },
      expandedPayloads: {},
      expandedResults: {},
      expandedExceptions: {},
      interval: null
    }
  },
  computed: {
    recentJobs() {
      return [...this.failedJobs, ...this.successfulJobs]
        .sort((a, b) => {
          const dateA = a.failed_at || a.finished_at
          const dateB = b.failed_at || b.finished_at
          return new Date(dateB) - new Date(dateA)
        })
    }
  },
  mounted() {
    this.fetchData()
    this.interval = setInterval(this.fetchData, 5000)
  },
  beforeUnmount() {
    if (this.interval) {
      clearInterval(this.interval)
    }
  },
  methods: {
    async fetchData() {
      try {
        const response = await fetch('/admin/api/jobs')
        const data = await response.json()
        this.pendingJobs = data.pendingJobs
        this.failedJobs = data.failedJobs
        this.successfulJobs = data.successfulJobs
        this.stats = data.stats
        this.tenants = data.tenants
      } catch (error) {
        console.error('Error fetching jobs:', error)
      }
    },
    formatPayload(payload) {
      if (!payload) return ''
      try {
        const decoded = typeof payload === 'string' ? JSON.parse(payload) : payload
        return JSON.stringify(decoded, null, 2)
      } catch (e) {
        return payload
      }
    },
    formatResult(result) {
      if (!result) return ''
      try {
        const decoded = typeof result === 'string' ? JSON.parse(result) : result
        if (!Array.isArray(decoded)) return JSON.stringify(decoded, null, 2)
        
        return decoded.map(log => {
          const date = new Date(log.date).toLocaleString('fr-FR', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
          }).replace(',', '')
          
          const dataStr = log.data && Object.keys(log.data).length > 0 
            ? JSON.stringify(log.data)
            : ''
            
          return `[${date}] ${log.level.toUpperCase()}: ${log.message}${dataStr ? ' ' + dataStr : ''}`
        }).join('\n')
      } catch (e) {
        return result
      }
    },
    truncatePayload(payload) {
      if (!payload) return ''
      const formatted = this.formatPayload(payload)
      return formatted.length > 100 ? formatted.substring(0, 100) + '...' : formatted
    },
    truncateResult(result) {
      if (!result) return ''
      const formatted = this.formatResult(result)
      return formatted.length > 100 ? formatted.substring(0, 100) + '...' : formatted
    },
    truncateException(exception) {
      if (!exception) return ''
      return exception.length > 100 ? exception.substring(0, 100) + '...' : exception
    },
    togglePayload(id) {
      this.expandedPayloads = {
        ...this.expandedPayloads,
        [id]: !this.expandedPayloads[id]
      }
    },
    toggleResult(id) {
      this.expandedResults = {
        ...this.expandedResults,
        [id]: !this.expandedResults[id]
      }
    },
    toggleException(id) {
      this.expandedExceptions = {
        ...this.expandedExceptions,
        [id]: !this.expandedExceptions[id]
      }
    },
    formatDate(date) {
      if (!date) return ''
      return new Date(date).toLocaleString('fr-FR')
    },
    getLogLevelClass(level) {
      const classes = {
        'ERROR': 'bg-red-50 dark:bg-red-900/20',
        'WARNING': 'bg-yellow-50 dark:bg-yellow-900/20',
        'INFO': 'bg-blue-50 dark:bg-blue-900/20',
        'DEBUG': 'bg-gray-50 dark:bg-gray-900/20'
      }
      return classes[level] || 'bg-gray-50 dark:bg-gray-900/20'
    },
    getLogLevelBadgeClass(level) {
      const classes = {
        'ERROR': 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
        'WARNING': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
        'INFO': 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
        'DEBUG': 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300'
      }
      return classes[level] || 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300'
    },
    formatException(exception) {
      if (!exception) return ''
      try {
        // Si c'est une exception sérialisée PHP
        if (exception.startsWith('O:')) {
          // Extraction des informations principales
          const classMatch = exception.match(/O:(\d+):"([^"]+)"/)
          if (classMatch) {
            const className = classMatch[2]
            const properties = exception.split('{').slice(1).join('{')
            const formattedProperties = properties
              .split(';')
              .map(prop => {
                const [type, value] = prop.split(':')
                if (!type || !value) return ''
                return `  ${type}: ${value}`
              })
              .filter(Boolean)
              .join('\n')
            
            return `Classe: ${className}\nPropriétés:\n${formattedProperties}`
          }
        }
        
        // Si c'est du JSON
        const decoded = JSON.parse(exception)
        return JSON.stringify(decoded, null, 2)
      } catch (e) {
        return exception
      }
    },
    getDisplayName(payload) {
      if (!payload) return '-'
      try {
        const decoded = typeof payload === 'string' ? JSON.parse(payload) : payload
        const displayName = decoded.displayName || '-'
        if (displayName === '-') return displayName
        return displayName.split('\\').pop()
      } catch (e) {
        return '-'
      }
    },
    getTenantName(payload) {
      if (!payload) return '-'
      try {
        const decoded = typeof payload === 'string' ? JSON.parse(payload) : payload
        const tenantId = decoded?.data?.command?.tenant?.id
        if (!tenantId) return '-'
        const tenant = this.tenants[tenantId]
        return tenant ? `${tenant.name} (ID: ${tenantId})` : `Inconnu (ID: ${tenantId})`
      } catch (e) {
        return '-'
      }
    }
  }
}
</script> 