package edu.tamucc.hri.griidc.exception;

import java.util.Collections;
import java.util.HashMap;
import java.util.Iterator;
import java.util.Map;
import java.util.Map.Entry;
import java.util.Vector;

public class GriidcExceptionService {

	/**
	 * a map of Exception type to failure return codes
	 */
	private static GriidcExceptionService instance = null;
	private static Map<String, Integer> exceptionCodeMap = Collections
			.synchronizedMap(new HashMap<String, Integer>());
	private static String[] keyValuePairStrings = null;

	private GriidcExceptionService() {
		init();
	}

	/** singleton implementation */
	public static GriidcExceptionService getInstance() {
		if (instance == null)
			GriidcExceptionService.instance = new GriidcExceptionService();
		return GriidcExceptionService.instance;
	}

	private void init() {
		for (int i = 0; i < keys.length && i < values.length; i++) {
			System.err.println("loading key: " + keys[i] + " - " + values[i]);
			GriidcExceptionService.exceptionCodeMap.put(keys[i], new Integer(values[i]));
		}
	}

	public static  String[] getKeyValuePairs() {
		if (GriidcExceptionService.keyValuePairStrings == null) {
			Iterator<Entry<String, Integer>> it = GriidcExceptionService.exceptionCodeMap.entrySet()
					.iterator();
			Vector<Entry<String, Integer>> v = new Vector<Entry<String, Integer>>();

			while (it.hasNext()) {
				v.add(it.next());
			}
			GriidcExceptionService.keyValuePairStrings = new String[v.size()];
			int i = 0;
			it = v.iterator();
			while (it.hasNext()) {
				Entry<String, Integer> e;
				e = it.next();
				GriidcExceptionService.keyValuePairStrings[i++] = e.getKey() + ", "
						+ e.getValue().intValue();
			}
		}
		return GriidcExceptionService.keyValuePairStrings;
	}

	public static  void printKeyValuePairs() {
		String[] strings = GriidcExceptionService.getKeyValuePairs();
		System.out.println("GriidcExceptionConstants key value pairs");

		for (int i = 0; i < strings.length; i++) {
			System.out.println(strings[i]);
		}
	}

	public static String getShortClassName(Class c) {
		String longName = c.getCanonicalName();
		int ndx = longName.lastIndexOf('.') + 1;
		return longName.substring(ndx);
	}

	public static  int getTerminationCodeForException(Exception e) {
		String k = GriidcExceptionService.getShortClassName(e.getClass());
		if(GriidcExceptionService.exceptionCodeMap.get(k) != null) 
		   return GriidcExceptionService.exceptionCodeMap.get(k);
		return UnknowExceptionValue;
	}
	public static  void fatalException(Exception e, String msg) {

		System.err.println("GriidcException.fatalExceeption() " + msg);
		System.err.println("message: " + e.getMessage());
		System.err.println("termination code: " + getTerminationCodeForException(e));
		System.exit(getTerminationCodeForException(e));
	}

	public static int UnknowExceptionValue = -1;
	public static int[] values = { -2, -3, -4, -5 };
	public static String[] keys = { "FileNotFoundException", "SQLException",
			"ClassNotFoundException", "PropertyNotFoundException" };

	public static void main(String[] args) {
		GriidcExceptionService.printKeyValuePairs();
	}
}
