import request from 'supertest'
import { createApp } from '../app'
import { env } from '../config/env'

const app = createApp()

describe('System API', () => {
  describe('GET /api/v1/health', () => {
    it('should return health status', async () => {
      const response = await request(app).get(`${env.API_PREFIX}/health`).expect(200)

      expect(response.body.status).toBe('ok')
      expect(response.body.timestamp).toBeDefined()
    })
  })

  describe('GET /api/v1/health/ready', () => {
    it('should return readiness status', async () => {
      const response = await request(app).get(`${env.API_PREFIX}/health/ready`)

      expect(response.body.status).toBeDefined()
      expect(response.body.timestamp).toBeDefined()
      expect(response.body.checks).toBeDefined()
    })
  })

  describe('GET /api/v1/health/live', () => {
    it('should return liveness status', async () => {
      const response = await request(app).get(`${env.API_PREFIX}/health/live`).expect(200)

      expect(response.body.status).toBe('alive')
      expect(response.body.timestamp).toBeDefined()
    })
  })

  describe('GET /api/v1/', () => {
    it('should return API welcome message', async () => {
      const response = await request(app).get(`${env.API_PREFIX}/`).expect(200)

      expect(response.body.message).toBeDefined()
      expect(response.body.version).toBeDefined()
      expect(response.body.timestamp).toBeDefined()
    })
  })

  describe('GET /api/v1/version', () => {
    it('should return version information', async () => {
      const response = await request(app).get(`${env.API_PREFIX}/version`).expect(200)

      expect(response.body.version).toBeDefined()
      expect(response.body.apiVersion).toBeDefined()
      expect(response.body.nodeVersion).toBeDefined()
      expect(response.body.environment).toBeDefined()
    })
  })

  describe('GET /api/v1/ping', () => {
    it('should return pong', async () => {
      const response = await request(app).get(`${env.API_PREFIX}/ping`).expect(200)

      expect(response.body.message).toBe('pong')
      expect(response.body.timestamp).toBeDefined()
    })
  })

  describe('GET /api/v1/status', () => {
    it('should return system status', async () => {
      const response = await request(app).get(`${env.API_PREFIX}/status`).expect(200)

      expect(response.body.status).toBe('operational')
      expect(response.body.uptime).toBeDefined()
      expect(response.body.memory).toBeDefined()
      expect(response.body.process).toBeDefined()
    })
  })
})
