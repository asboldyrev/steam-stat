import axios from 'axios';

const api = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

export function useApi() {
    const getDashboardStats = async () => {
        try {
            const { data } = await api.get('/dashboard/stats');
            return data;
        } catch (error) {
            console.error('Failed to fetch dashboard stats:', error);
            throw error;
        }
    };

    const getPlatformDistribution = async () => {
        try {
            const { data } = await api.get('/dashboard/platform-distribution');
            return data;
        } catch (error) {
            console.error('Failed to fetch platform distribution:', error);
            throw error;
        }
    };

    const getRecentActivity = async () => {
        try {
            const { data } = await api.get('/dashboard/recent-activity');
            return data;
        } catch (error) {
            console.error('Failed to fetch recent activity:', error);
            throw error;
        }
    };

    const getTopGames = async () => {
        try {
            const { data } = await api.get('/dashboard/top-games');
            return data;
        } catch (error) {
            console.error('Failed to fetch top games:', error);
            throw error;
        }
    };

    const getGames = async (params = {}) => {
        try {
            const { data } = await api.get('/games', { params });
            return data;
        } catch (error) {
            console.error('Failed to fetch games:', error);
            throw error;
        }
    };

    const getGame = async (id) => {
        try {
            const { data } = await api.get(`/games/${id}`);
            return data;
        } catch (error) {
            console.error(`Failed to fetch game ${id}:`, error);
            throw error;
        }
    };

    const getGamePlatformBreakdown = async (id) => {
        try {
            const { data } = await api.get(`/games/${id}/platform-breakdown`);
            return data;
        } catch (error) {
            console.error(`Failed to fetch platform breakdown for game ${id}:`, error);
            throw error;
        }
    };

    const getGamePlaytimeHistory = async (id) => {
        try {
            const { data } = await api.get(`/games/${id}/playtime-history`);
            return data;
        } catch (error) {
            console.error(`Failed to fetch playtime history for game ${id}:`, error);
            throw error;
        }
    };

    const getGameRecentSessions = async (id) => {
        try {
            const { data } = await api.get(`/games/${id}/recent-sessions`);
            return data;
        } catch (error) {
            console.error(`Failed to fetch recent sessions for game ${id}:`, error);
            throw error;
        }
    };

    const getLastSync = async () => {
        try {
            const { data } = await api.get('/sync/last');
            return data;
        } catch (error) {
            console.error('Failed to fetch last sync:', error);
            throw error;
        }
    };

    const triggerSync = async () => {
        try {
            const { data } = await api.post('/sync/trigger');
            return data;
        } catch (error) {
            console.error('Failed to trigger sync:', error);
            throw error;
        }
    };

    return {
        api,
        getDashboardStats,
        getPlatformDistribution,
        getRecentActivity,
        getTopGames,
        getGames,
        getGame,
        getGamePlatformBreakdown,
        getGamePlaytimeHistory,
        getGameRecentSessions,
        getLastSync,
        triggerSync,
    };
}